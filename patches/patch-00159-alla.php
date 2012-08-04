<?php
// Create a search index for Items. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/item');
$db = new db_alloc();
$q = prepare("SELECT * FROM item");
$db->query($q);
while ($db->row()) {
  $item = new item();
  $item->read_db_record($db);
  $item->update_search_index_doc($index);
}
$index->commit();
?>
