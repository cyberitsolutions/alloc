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


define("IN_LOGIN_RIGHT_NOW",true);
require_once("../alloc.php");

// If we already have a session
if ($sess->Started()) {
  $url = $sess->GetUrl($TPL["url_alloc_home"]);
  header("Location: ".$url);
  exit();

// Else log the user in
} else if ($_POST["login"]) {

  $person = new person;
  $row = $person->get_valid_login_row($_POST["username"],$_POST["password"]);

  if ($row) {

    $sess->Start($row);

    $q = sprintf("UPDATE person SET lastLoginDate = '%s' WHERE personID = %d"
                 ,date("Y-m-d H:i:s"),$row["personID"]);
    $db = new db_alloc;
    $db->query($q);
                   

    if ($sess->TestCookie()) {
      $sess->UseCookie();
    } else {
      $sess->UseGet();
    }

    $url = $sess->GetUrl($TPL["url_alloc_home"]);
    $sess->Save();
    header("Location: ".$url);
  }
  $error = "<p class='error'>Invalid Username or Password.</p>";

} else if ($_POST["new_pass"]) {

  $db = new db_alloc;
  $db->query(sprintf("SELECT * FROM person WHERE username = '%s' AND emailAddress = '%s'"
                    ,db_esc($_POST["username"]), db_esc($_POST["email"])));

  if ($db->next_record()) {
    $current_user = new person;
    $current_user->read_db_record($db);

    // generate new random password
    $password = "";
    $pwSource = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?";
    srand((float) microtime() * 1000000);
    for ($i = 0; $i < 8; $i++) {
      $password.= substr($pwSource, rand(0, strlen($pwSource)), 1);
    }
    $current_user->set_value('password', db_esc(crypt(trim($password), trim($password))));
    $current_user->save();
    $mail = mail($email, "New Alloc password request", "Your new temporary password: ".$password, "From: ".ALLOC_DEFAULT_FROM_ADDRESS);
    $error = "New password sent to: ".$_POST["email"];
  } else {
    $error = "<p class='error'>Invalid Username or Email Address.</p>";
  }


// Else if just visiting the page
} else {
  $sess->SetTestCookie();
}

$account = $_POST["account"] or $account = $_GET["account"];
$TPL["account"] = $account;

if ($error) {
  $TPL["error"] = $error; 
} else {
  $TPL["error"] = "Please enter your:";
}


if (!isset($account)) { 
  $TPL["links"] = "Login | <a href=\"".$TPL["url_alloc_login"]."?account=true\">New Password</a>";
} else {
  $TPL["links"] = "<a href=\"".$TPL["url_alloc_login"]."\">Login</a> | New Password";
}

$TPL["username"] = $_POST["username"];


if (!isset($account)) { 
  $TPL["password_or_email_address_field"] = "<td class=\"right\">Password&nbsp;&nbsp;</td>";
  $TPL["password_or_email_address_field"].= "<td class=\"right\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"32\"></td>";
  $TPL["login_or_send_pass_button"] = "<input type=\"submit\" name=\"login\" value=\"&nbsp;&nbsp;Login&nbsp;&nbsp;\">";
} else { 
  $TPL["password_or_email_address_field"] = "<td class=\"right\">Email Address&nbsp;&nbsp;</td>";
  $TPL["password_or_email_address_field"].= "<td class=\"right\"><input type=\"text\" name=\"email\" size=\"20\" maxlength=\"32\"></td>";
  $TPL["login_or_send_pass_button"] = "<input type=\"submit\" name=\"new_pass\" value=\"Send Password\">";
}


$TPL["status_line"] = ALLOC_TITLE." ".ALLOC_VERSION." &copy; 2006 <a href=\"http://www.cybersource.com.au\">Cybersource</a>"; 
$TPL["ALLOC_SHOOER"] = ALLOC_SHOOER; 


include_template("templates/login.tpl");

page_close();

?>
