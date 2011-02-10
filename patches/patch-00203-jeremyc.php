<?php

// Create the ATTACHMENTS_DIR/tmp folder for holding CSV uploads.

$dir = ATTACHMENTS_DIR . 'tmp';
@mkdir($dir, 0777);

?>
