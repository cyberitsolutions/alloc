<?php
include("alloc.inc");
check_entity_perm("person", PERM_PERSON_SEND_EMAIL);

$email_to = "";

reset($selected_persons);
while (list(, $person_id) = each($selected_persons)) {
  $person = new person;
  $person->set_id($person_id);
  $person->select();
  if ($email_to) {
    $email_to.= ",";
  }
  $email_to.= $person->get_value("emailAddress");
}

$current_user = new person;
$current_user->set_id($auth->auth["uid"]);
$current_user->select();

$TPL["email_from"] = $current_user->get_value("emailAddress");
$TPL["email_to"] = $email_to;
include_template("templates/personsEmail.tpl");

page_close();



?>
