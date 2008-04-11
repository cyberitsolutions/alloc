<?php

// make the new "whatsnew" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."whatsnew")) {
  mkdir(ATTACHMENTS_DIR."whatsnew",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."whatsnew")) {
  echo "Please manually create a directory called \"whatsnew\" in the file attachment
  upload directory for alloc. (This is the same directory that has \"task\",
  \"project\" and \"client\" upload directories).";

} else if (!is_writeable(ATTACHMENTS_DIR."whatsnew")) {
  echo "Please ensure this new \"whatsnew\" directory is writeable by the webserver.";
}

?>
