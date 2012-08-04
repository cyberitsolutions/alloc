<?php
// Create a search index for Tasks. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/task');
$db = new db_alloc();
$q = prepare("SELECT * FROM task");
$db->query($q);
while ($db->row()) {
  $task = new task();
  $task->read_db_record($db);
  $task->update_search_index_doc($index);
}
$index->commit();
?>
