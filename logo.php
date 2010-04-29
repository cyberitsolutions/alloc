<?php
include('alloc_config.php');
$path = ATTACHMENTS_DIR.'logos/';
$image = 'logo_small.png';
$type = 'png';
if(isset($_GET['size'])) {
  if($_GET['size'] == 'big') {
    $image = 'logo.png';
  } else if($_GET['size'] == 'jpg') {
    $type = 'jpeg';
    $image = 'logo.jpg';
  }
}
header('Content-type: image/'.$type);
echo file_get_contents($path.$image);
?>
