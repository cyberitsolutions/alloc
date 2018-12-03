<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
 * Pty. Ltd.
 *
 * This file is part of the allocPSA application <info@cyber.com.au>.
 *
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 *
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

require_once("../alloc.php");

/*
Username,
First_Name,
Surname,
Password,
E-mail,
Phone No,
Comments
*/

$cur = config::get_config_item("currency");

$row = 1;
if (($handle = fopen("../../David_People.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

      foreach( $data as $key => $val ) {
      #  $data[$key] = utf8_encode($data[$key]);
      }

      $person = new person();
      $person->currency = $cur;
      $person->set_value("username",$data[0]);
      $person->set_value("firstName",$data[1]);
      $person->set_value("surname",$data[2]);
      $person->set_value("password",password_hash($data[3], PASSWORD_DEFAULT));
      $person->set_value("emailAddress",$data[4]);
      $person->set_value("phoneNo1",$data[5]);
      $person->set_value("comments",$data[6]);
      $person->set_value("perms","employee");
      $person->set_value("personActive",1);
      $person->set_value("personModifiedUser",$current_user->get_id());
      $person->save();

      $x++;
      echo "<br>here: ".$person->get_id().$data[0];
      if ($x>4) {
        //die();
      }
    }
    fclose($handle);
}



?>
