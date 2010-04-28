<?php
include('alloc_config.php');
header('Content-type: image/png');
echo file_get_contents(ATTACHMENTS_DIR.'/logos/logo_small.png');
?>
