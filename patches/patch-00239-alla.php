<?php
// This may take a looong time


// nuke output buffering
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"512M");


foreach (array("client","comment","item","project","task","timeSheet","wiki") as $i) {
  flush();
  //echo "<br>Optimizing index: ".$i." ... ";
  $index = Zend_Search_Lucene::open(ATTACHMENTS_DIR.'search/'.$i);
  $index->optimize();
  //echo "done.";
  flush();
}

?>
