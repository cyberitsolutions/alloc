<?php

// make the new "comment" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."comment")) {
  mkdir(ATTACHMENTS_DIR."comment",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."comment")) {
  echo "Please manually create a directory called \"comment\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."comment")) {
  echo "Please ensure this new \"comment\" directory is writeable by the webserver.";
}

?>
