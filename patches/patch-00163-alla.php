<?php
// Create a search index for Comments. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/comment');
$db = new db_alloc();
$q = prepare("SELECT * FROM comment");
$db->query($q);
while ($db->row()) {
  $comment = new comment();
  $comment->read_db_record($db);
  $comment->update_search_index_doc($index);
}
$index->commit();
?>
