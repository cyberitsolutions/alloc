<?php
// Create a search index for Time Sheets. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/timeSheet');
$db = new db_alloc();
$q = prepare("SELECT * FROM timeSheet");
$db->query($q);
while ($db->row()) {
  $timeSheet = new timeSheet();
  $timeSheet->read_db_record($db);
  $timeSheet->update_search_index_doc($index);
}
$index->commit();
?>
