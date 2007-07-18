<?php

// make the new "backups" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."backups")) {
  mkdir(ATTACHMENTS_DIR."backups",0777);
  mkdir(ATTACHMENTS_DIR."backups".DIRECTORY_SEPARATOR."0", 0777);
} 

if (!is_dir(ATTACHMENTS_DIR."backups".DIRECTORY_SEPARATOR."0")) {
  echo "Please manually create the directory \"" . ATTACHMENTS_DIR."backups".DIRECTORY_SEPARATOR."0". "\"";

} else if (!is_writeable(ATTACHMENTS_DIR."backups".DIRECTORY_SEPARATOR."0")) {
  echo "Please ensure this new \"backups/0\" directory is writeable by the webserver.";
}

?>
