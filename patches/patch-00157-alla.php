<?php

// make the new "search" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."search")) {
  mkdir(ATTACHMENTS_DIR."search",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."search")) {
  echo "Please manually create a directory called \"search\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."search")) {
  echo "Please ensure this new \"search\" directory is writeable by the webserver.";
}

?>
