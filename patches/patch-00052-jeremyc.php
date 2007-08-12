<?php

// make the new "backups" directory
if (!defined("ATTACHMENTS_DIR")) {
  echo "ATTACHMENTS_DIR is not defined!";
}

if (!is_dir(ATTACHMENTS_DIR."backups")) {
  mkdir(ATTACHMENTS_DIR."backups",0777);
} 

if (!is_dir(ATTACHMENTS_DIR."backups")) {
  echo "Please manually create the directory \"" . ATTACHMENTS_DIR."backups\"";

} else if (!is_writeable(ATTACHMENTS_DIR."backups")) {
  echo "Please ensure this new \"backups\" directory is writeable by the webserver.";
}

?>
