<?php
namespace gusenru;

use ghopper\CNestedSet;

class CCommentsTree extends CNestedSet
{
    function commentsList($tree_id) {
    	$aComments = array();

        $q = sprintf("
			SELECT 
			    nested_tree.id,
			    nested_tree.depth,
			    comments.user_id,
			    comments.type,
			    comments.name,
			    comments.comment,
			    comments.approved,
			    comments.date
			FROM (
				SELECT
                    node.*,
                    (COUNT(parent.{$this->tbId}) - (sub_tree.depth + 1)) AS depth
                FROM {$this->tbName} AS node,
                    {$this->tbName} AS parent,
                    {$this->tbName} AS sub_parent,
                    (
                        SELECT node.*,
                        (COUNT(parent.id) - 1) AS depth
                        FROM {$this->tbName} AS node,
                            {$this->tbName} AS parent
                        WHERE node.{$this->tbLeft}
                            BETWEEN parent.{$this->tbLeft}
                            AND parent.{$this->tbRight}
                        AND node.{$this->tbId} = %d
                        GROUP BY node.id
                        ORDER BY node.{$this->tbLeft}
                    ) AS sub_tree
                WHERE node.{$this->tbLeft} 
                    BETWEEN parent.{$this->tbLeft} AND parent.{$this->tbRight}
                        AND node.{$this->tbLeft}
                            BETWEEN sub_parent.{$this->tbLeft} 
                            AND sub_parent.{$this->tbRight}
                        AND sub_parent.{$this->tbId} = sub_tree.{$this->tbId}
                GROUP BY node.{$this->tbId}
                ORDER BY node.{$this->tbLeft}) nested_tree
			JOIN comments ON comments.node_id=nested_tree.id",
            $tree_id
        );
		$aRes = $this->pdo->query($q)->fetchAll(\PDO::FETCH_ASSOC);

		// begin with 1 due to the first fow is a unit comments root
        for ($i=0;$i<count($aRes);$i++) {
			$aComments["comments"]["comment{$i}"]['text'] = $aRes[$i]['comment'];
			$aComments["comments"]["comment{$i}"]['name'] = $aRes[$i]['name'];
			$aComments["comments"]["comment{$i}"]['depth'] = $aRes[$i]['depth'];
        	$aComments["comments"]["comment{$i}"]['@attributes'] = array(
        		'id' => $aRes[$i]['id'],
        		'user_id' => $aRes[$i]['user_id'],
        		'type' => $aRes[$i]['type'],
        		'approved' => ($aRes[$i]['approved'] === '0') ?
        			'FALSE' : 'TRUE'
    		);
        }

        return $aComments;
    }

    function addComment($parent_tree_id, $user_id, $user_type,
    		$user_name, $comment) {
        $this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, FALSE);
        $this->pdo->beginTransaction();
        $this->pdo->exec("LOCK TABLES comments_tree WRITE");
        
		try {
			$com_id = $this->addChild($parent_tree_id);
	
	        $stmt = $this->pdo->prepare("
	    		INSERT 
	    		INTO comments (
	    			node_id,
	    			user_id,
	    			type,
	    			name,
	    			comment
	    		) 
	    		VALUES (:node_id, :uid, :type, :name, :comment)");
	        $stmt->bindValue(':node_id', $com_id, \PDO::PARAM_INT);
	        $stmt->bindValue(':uid', $user_id, \PDO::PARAM_STR);
	        $stmt->bindValue(':type', $user_type, \PDO::PARAM_STR);
	        $stmt->bindValue(':name', $user_name, \PDO::PARAM_STR);
	        $stmt->bindValue(':comment', $comment, \PDO::PARAM_STR);
	        $stmt->execute();

			$this->pdo->commit();
		} catch(\Exception $ex) {
			$this->pdo->rollBack();
			throw new \Exception($ex);
		} finally {
			$this->pdo->exec('UNLOCK TABLES');
		}

		$this->pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, TRUE);
    }

    function deleteComments($tree_id) {
		$aTree = $this->getTree($tree_id);

        $stmt = $this->pdo->prepare('
			DELETE FROM comments
			WHERE node_id=:node_id
    	');
		foreach ($aTree AS $value) {
	    	$stmt->bindParam(':node_id', $value['id'], \PDO::PARAM_INT);
	    	$stmt->execute();
		}
		$this->deleteTree($tree_id);
    }
}