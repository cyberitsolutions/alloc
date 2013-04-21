<?php

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");

$db = new db_alloc();

// Loop through attachments directory
$dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR;
if (is_dir($dir)) {
  $handle = opendir($dir);
  while (false !== ($file = readdir($handle))) {
    clearstatcache();
    if ($file == "." || $file == ".." || !is_numeric($file) || dir_is_empty($dir.$file) || !is_numeric($file)) {
      continue;
    }

    // Figure out which email created the comment
    $comment = new comment();
    $comment->set_id($file);
    $comment->select();

    echo "<br><br><hr>Examining comment ".$file;

    // Figure out what the mime parts are for the attachments and update comment.commentMimeParts
    list($email,$text,$mimebits) = $comment->find_email(true);


    if (!$email) {
      echo "<br>Couldn't find email for commentID: ".$file."<br>";
      rename($dir.$file, $dir."fail_".$file);
    }

    if ($mimebits) {
      echo "<br>Setting commentMimeParts for comment: ".$comment->get_id();
      $comment->set_value("commentMimeParts",serialize($mimebits));
      $comment->skip_modified_fields = true;
      $comment->save();
      rename($dir.$file, $dir."done_".$file);
    }
  }
}

?>
