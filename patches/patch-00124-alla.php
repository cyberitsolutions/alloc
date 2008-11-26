<?php

// make the new "wiki" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."wiki")) {
  mkdir(ATTACHMENTS_DIR."wiki",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."wiki")) {
  echo "Please manually create a directory called \"wiki\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."wiki")) {
  echo "Please ensure this new \"wiki\" directory is writeable by the webserver.";
}

?>
