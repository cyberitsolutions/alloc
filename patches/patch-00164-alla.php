<?php
// Create a search index for Wiki documents. This patch may take a long time to apply.
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");
$index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/wiki');
$allowed_suffixes = array("",".text",".txt",".html",".xml",".mdwn",".pdf");
$files = search::get_recursive_dir_list(wiki_module::get_wiki_path());
foreach ($files as $file) {
  // check that the file is of an allowable type.
  preg_match("/(\.\w{3,4}$)/",$file,$m);
  if (!in_array($m[1],$allowed_suffixes)) 
    continue;
  wiki_module::update_search_index_doc($index,str_replace(wiki_module::get_wiki_path(),"",$file));
}
$index->commit();
?>
