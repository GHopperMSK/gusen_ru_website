<?php
namespace gusenru\module;

class CUserMod extends \gusenru\CMod
{
    function __construct($param1, $param2) {
    	\gusenru\CWebPage::debug();
    	
    	parent::__construct($param1, $param2);

        $this->_commentForm();
    }
    
    private function _getUserDataFromSession() {
    	$aUser = array();
    	$aUser['user']['@attributes'] = array(
    		'name' => $_SESSION["user"]["name"],
    		'type' => $_SESSION["user"]["type"],
    		'id' => $_SESSION["user"]["id"]
		);
		$aUser['user']['image'] = $_SESSION["user"]["photo"];
    	
    	$this->_addContent($aUser);
    }

    private function _commentForm() {
    	$hWebPage = \gusenru\CWebPage::getInstance();
    	
        if (isset($_SESSION["user"])) {
            $this->_getUserDataFromSession();
            if (isset($_SESSION["user_referer"]))
                unset($_SESSION["user_referer"]);
            
            $this->_addContent(array('unit' => $hWebPage->getGetValue('id')));
        }
        else {
            list($realHost,)=explode(':',$_SERVER['HTTP_HOST']);

            $cur_link = sprintf("https://%s/%s/%d#comment_form",
                $realHost,
                $hWebPage->getGetValue('page'),
                $hWebPage->getGetValue('id')
            );

            $_SESSION["user_referer"] = $cur_link;

			// vkontakte auth url
			$vk = new \VK\VK(VK_CLIENT_ID, VK_SECRET);
			$vk->setApiVersion(VK_VERSION);
			$vkLink = $vk->getAuthorizeURL(
				'uid,first_name,last_name,sex,photo_50,email',
				sprintf("https://%s/?page=oauth_vk", $realHost)
			);

			// facebook auth url
			$fb = new \Facebook\Facebook([
			  'app_id'					=> FB_CLIENT_ID,
			  'app_secret'				=> FB_SECRET,
			  'default_graph_version'	=> FB_VERSION
			  ]);
			
			$helper = $fb->getRedirectLoginHelper();
			
			$permissions = ['public_profile, email'];
			$fbLink = $helper->getLoginUrl(
				sprintf("https://%s/?page=oauth_fb", $realHost), 
				$permissions
			);

			// google auth url
			$client = new \Google_Client();
			$client->setClientId(GL_CLIENT_ID);
			$client->setClientSecret(GL_SECRET);
			$client->setRedirectUri(sprintf("https://%s/?page=oauth_gl", 
				$realHost));
			
			$client->setScopes(array(
				'https://www.googleapis.com/auth/userinfo.email',
				'https://www.googleapis.com/auth/userinfo.profile')
			);
			
			$glLink = $client->createAuthUrl();

            // login form
            $aSocialList = array();
            $aSocialList['snetwork1']['link'] = $vkLink;
            $aSocialList['snetwork1']['@attributes']['type'] = 'vk';
            $aSocialList['snetwork2']['link'] = $glLink;
            $aSocialList['snetwork2']['@attributes']['type'] = 'gl';
            $aSocialList['snetwork3']['link'] = $fbLink;
            $aSocialList['snetwork3']['@attributes']['type'] = 'fb';

            $this->_addContent($aSocialList);
        }
    }
}

?>
