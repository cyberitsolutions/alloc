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


define("NO_AUTH",1);
require_once("../alloc.php");

$sess = new session();
if (isset($_POST["forwardUrl"])) {
  $url = $_POST["forwardUrl"];
} else if (isset($_GET["forward"])) {
  $url = $_GET["forward"];
} else {
  $url = $sess->GetUrl($TPL["url_alloc_home"]);
}

// If we already have a session
if ($sess->Started()) {
  alloc_redirect($url);
  exit();

// Else log the user in
} else if ($_POST["login"]) {

  $person = new person();
  $row = $person->get_valid_login_row($_POST["username"],$_POST["password"]);

  if ($row) {

    $sess->Start($row);

    $q = prepare("UPDATE person SET lastLoginDate = '%s' WHERE personID = %d"
                 ,date("Y-m-d H:i:s"),$row["personID"]);
    $db = new db_alloc();
    $db->query($q);
                   

    if ($sess->TestCookie()) {
      $sess->UseCookie();
      $sess->SetTestCookie($_POST["username"]);
    } else {
      $sess->UseGet();
    }

    $sess->Save();
    alloc_redirect($url);
  }
  $error = "Invalid username or password.";

} else if ($_POST["new_pass"]) {

  $db = new db_alloc();
  $db->query("SELECT * FROM person WHERE emailAddress = '%s'", $_POST["email"]);

  if ($db->next_record()) {
    // generate new random password
    $password = "";
    $pwSource = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?";
    srand((float) microtime() * 1000000);
    for ($i = 0; $i < 8; $i++) {
      $password.= substr($pwSource, rand(0, strlen($pwSource)), 1);
    }

    $q = prepare("UPDATE person SET password = '%s' WHERE emailAddress = '%s'
                 ",encrypt_password($password), $_POST["email"]);
    $db2 = new db_alloc();
    $db2->query($q);

    $e = new email_send($_POST["email"], "New Password", "Your new temporary password: ".$password, "new_password");
    #echo "Your new temporary password: ".$password;
    if ($e->send()) {
      $TPL["message_good"][] = "New password sent to: ".$_POST["email"];
    } else {
      $error = "Unable to send email.";
    }
  } else {
    $error = "Invalid email address.";
  }


// Else if just visiting the page
} else {
  if (!$sess->TestCookie()) {
    $sess->SetTestCookie();
  }
}

$error and alloc_error($error);

$account = $_POST["account"] or $account = $_GET["account"];
$TPL["account"] = $account;

if (isset($_POST["username"])) {
  $TPL["username"] = $_POST["username"];
} else if ($sess->TestCookie() != "alloc_test_cookie") {
  $TPL["username"] = $sess->TestCookie();
}

if (isset($_GET["forward"])) {
  $TPL["forward_url"] = strip_tags($_GET["forward"]);
}

$TPL["status_line"] = APPLICATION_NAME." ".get_alloc_version()." &copy; ".date("Y")." <a href=\"http://www.cyber.com.au\">Cyber IT Solutions</a>"; 


if (!is_dir(ATTACHMENTS_DIR."whatsnew".DIRECTORY_SEPARATOR."0")) {
  mkdir(ATTACHMENTS_DIR."whatsnew".DIRECTORY_SEPARATOR."0");
}

$files = get_attachments("whatsnew",0);

if (is_array($files) && count($files)) {
  while ($f = array_pop($files)) {
    // Only show entries that are newer that 4 weeks old
    if (format_date("U",basename($f["path"])) > mktime() - (60*60*24*28)) {
      $x++;
      if($x>3) break; 
      $str.= $br."<b>".$f["restore_name"]."</b>";
      $str.= "<br><ul>".trim(file_get_contents($f["path"]))."</ul>";
      $br = "<br><br>";
    }
  }
  $str and $TPL["latest_changes"] = $str;
}

$TPL["body_id"] = "login";
$TPL["main_alloc_title"] = "allocPSA login";

include_template("templates/login.tpl");

?>
