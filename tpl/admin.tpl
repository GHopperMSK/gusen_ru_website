<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>Агенство спецтехники Гусеница</title>
<script src="https://vk.com/js/api/openapi.js?137" type="text/javascript"></script>
<style>
.unit IMG {
    float: left;
}

.unit {
    clear: both;
    margin-top: 10px;
    margin-bottom: 10px;
}

</style>
</head>
<body>
<script type="text/javascript">
/*
VK.init({
	apiId: 5768859
});
VK.Auth.login(function(response) {
  if (response.session) {
    console.log('auth ok');
    if (response.settings) {
    	console.log(response.settings);
    }
  } else {
		console.log('auth failed');
  }
}, VK.access.PHOTOS);
function wallPost(name, text) {
	VK.Api.call('photos.createAlbum', {
			title: name,
			group_id: '-137789409'
		}, 
		function (data) {
			console.log(data);
		}
	);
*/	
/*	
	VK.Api.call('wall.post', {
		    owner_id: '-137789409',
		    friends_only: 0,
		    from_group: 1,
		    signed: 0,
		    message: text
		}, 
		function (data) {
			console.log(data);
		}
	);
*/	
//}

</script>

<a href="?page=admin&act=main">Home</a>
<a href="?page=admin&act=logout&msg=Successfully_Logged_out">Log out</a>
<a href="?page=admin&act=admin_unit_form">Add an unit</a>
<a href="?page=admin&act=unapproved_comments">Comments (%{comments_unapproved_total&null&0}%)</a>
<br /><br />
%{search_form&search_form.xsl&0&admin}%

%{unit_list&admin_unit_list.xsl&0}%
<section class="paginator">
%{unit_list_paginator&search_paginator.xsl&0&admin}%
</section>

</body>
</html>