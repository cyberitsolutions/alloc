<?php
require_once("alloc.inc");
check_entity_perm("person", PERM_PERSON_SEND_EMAIL);

mail($email_to, $email_subject, $email_message, "From: $email_from");
$url = $TPL["url_alloc_personList"];

page_close();
header("Location: $url");



?>
