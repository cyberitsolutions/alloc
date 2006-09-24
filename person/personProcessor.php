<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("../alloc.php");
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

$TPL["email_from"] = $current_user->get_value("emailAddress");
$TPL["email_to"] = $email_to;
include_template("templates/personsEmail.tpl");

page_close();



?>
