<?php

// make the new "logos" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."logos")) {
  mkdir(ATTACHMENTS_DIR."logos",0777);
}

if (!is_dir(ATTACHMENTS_DIR."logos")) {
  echo "Please manually create a directory called \"logos\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."logos")) {
  echo "Please ensure this new \"logos\" directory is writeable by the webserver.";
}

?>
