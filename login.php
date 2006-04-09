<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */


define("IN_LOGIN_RIGHT_NOW",true);
require_once("alloc.inc");

// Log the user in
if ($_POST["login"]) {
  if (!$sess->Started()) {
    $db = new db_alloc;

    $q = sprintf("SELECT * FROM person WHERE username = '%s'",db_esc($_POST["username"]));
    $db->query($q);
    $db->next_record();
    $salt = $db->f("password");

    $q = sprintf("SELECT * FROM person WHERE username = '%s' and password = '%s'"
                ,db_esc($_POST["username"]),db_esc(crypt(trim($_POST["password"]), $salt)));

    $db->query($q);

    if ($row = $db->row()) {

      $sess->Start($row["personID"]);
      $sess->Put("username" ,strtolower($row["username"]));
      $sess->Put("perms" ,$row["perms"]);
      $sess->Put("personID" ,$row["personID"]);

      if ($_POST["use_cookies"]) {
        $sess->UseCookie();
      } else {
        $sess->UseGet();
      }
      $url = $sess->GetUrl($TPL["url_alloc_home"]);
      $sess->Save();
      header("Location: ".$url);
    }
  } 
  $error = "Username or Password incorrect.";

} else if ($_POST["new_pass"] && $_POST["username"] && $_POST["email"]) {

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
    $mail = mail($email, "New Alloc password request", "Your new temporary password: ".$password, "From: AllocPSA <".ALLOC_DEFAULT_FROM_ADDRESS.">");
    $error = "New password sent to: ".$_POST["email"];
  } else {
    $error = "Invalid Username or Email Address.";
  }


}

$account = $_POST["account"] or $account = $_GET["account"];
$TPL["account"] = $account;

if ($error) {
  $TPL["error"] = $error; 
} else if ($account) {
  $TPL["error"] = "Please enter your Username and Email Address:";
} else if (!$account) {
  $TPL["error"] = "Please enter your Username and Password:";
}


if (!isset($account)) { 
  $TPL["links"] = "Login | <a href=\"".$TPL["url_alloc_login"]."?account=true\">New Password</a>";
} else {
  $TPL["links"] = "<a href=\"".$TPL["url_alloc_login"]."\"><nobr>Login</nobr></a> | New Password";
}

$TPL["username"] = $_POST["username"];


if (!isset($account)) { 
  $TPL["password_or_email_address_field"] = "<td class=\"right\">Password</td>";
  $TPL["password_or_email_address_field"].= "<td class=\"right\"><input type=\"password\" name=\"password\" size=\"25\" maxlength=\"32\"></td>";
  $TPL["use_cookies"] = "Use Cookies <input type=\"checkbox\" name=\"use_cookies\" value=\"1\">";
  $TPL["login_or_send_pass_button"] = "<input type=\"submit\" name=\"login\" value=\"&nbsp;&nbsp;Login&nbsp;&nbsp;\">";
} else { 
  $TPL["password_or_email_address_field"] = "<td class=\"right\"><nobr>Email</nobr></td>";
  $TPL["password_or_email_address_field"].= "<td class=\"right\"><input type=\"text\" name=\"email\" size=\"25\" maxlength=\"32\"></td>";
  $TPL["login_or_send_pass_button"] = "<input type=\"submit\" name=\"new_pass\" value=\"Send Password\">";
}


$TPL["status_line"] = ALLOC_TITLE." ".ALLOC_VERSION." on ".$_SERVER["SERVER_NAME"]." ".ALLOC_DB_NAME." database at ".date("g:ia dS M"); 
$TPL["ALLOC_SHOOER"] = ALLOC_SHOOER; 


include_template("login.tpl");

page_close();

?>
