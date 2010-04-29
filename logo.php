<?php
include('alloc_config.php');
header('Content-type: image/png');
if(isset($_GET['size']) && $_GET['size'] == 'big') {
  echo file_get_contents(ATTACHMENTS_DIR.'/logos/logo.png');
} else {
  echo file_get_contents(ATTACHMENTS_DIR.'/logos/logo_small.png');
}
?>
