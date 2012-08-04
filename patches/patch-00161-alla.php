<?php
// Create a search index for Projects. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/project');
$db = new db_alloc();
$q = prepare("SELECT * FROM project");
$db->query($q);
while ($db->row()) {
  $project = new project();
  $project->read_db_record($db);
  $project->update_search_index_doc($index);
}
$index->commit();
?>
