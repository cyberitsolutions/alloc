<?php

// make the new "invoice" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."invoice")) {
  mkdir(ATTACHMENTS_DIR."invoice",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."invoice")) {
  echo "Please manually create a directory called \"invoice\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."invoice")) {
  echo "Please ensure this new \"invoice\" directory is writeable by the webserver.";
}

?>
