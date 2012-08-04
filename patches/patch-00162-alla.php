<?php
// Create a search index for Clients. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/client');
$db = new db_alloc();
$q = prepare("SELECT * FROM client");
$db->query($q);
while ($db->row()) {
  $client = new client();
  $client->read_db_record($db);
  $client->update_search_index_doc($index);
}
$index->commit();
?>
