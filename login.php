<?php

define("IN_LOGIN_RIGHT_NOW",true);
include("alloc.inc");

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
        echo "implement cookies!";
        $sess->UseCookies();
      } 

      $url = $sess->GetUrl($TPL["url_alloc_index"]);
      $sess->Save();
      header("Location:".$url);
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
    $mail = mail($email, "New Alloc password request", "Your new temporary password: ".$password, "From: Alloc <alloc-admin@cyber.com.au>");
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
  $TPL["login_or_send_pass_button"] = "Use Cookies <input type=\"checkbox\" name=\"use_cookies\" value=\"1\">";
  $TPL["login_or_send_pass_button"].= "<input type=\"submit\" name=\"login\" value=\"&nbsp;&nbsp;Login&nbsp;&nbsp;\">";
} else { 
  $TPL["password_or_email_address_field"] = "<td class=\"right\"><nobr>Email</nobr></td>";
  $TPL["password_or_email_address_field"].= "<td class=\"right\"><input type=\"text\" name=\"email\" size=\"25\" maxlength=\"32\"></td>";
  $TPL["login_or_send_pass_button"] = "<input type=\"submit\" name=\"new_pass\" value=\"Send Password\">";
}


$TPL["status_line"] = ALLOC_TITLE." ".ALLOC_VERSION." on ".$_SERVER["SERVER_NAME"]." ".ALLOC_DB_NAME." database at ".date("g:ia dS M"); 
$TPL["ALLOC_SHOOER"] = ALLOC_SHOOER; 


include_template("login.tpl");
exit;

  function auth_validatelogin() {
    global $new_pass, $login, $email, $username, $password, $current_user, $sess;

    // provides access for loginform.inc.php
    isset($username) and $this->auth["uname"] = $username;

    // Sending a new password to user
    if (isset($new_pass) && isset($username) && $username != "" && isset($email) && $email != "") {
      $this->db->query(sprintf("SELECT * FROM %s WHERE username = '%s' AND emailAddress = '%s'", $this->database_table, db_esc($username), db_esc($email)));
      if ($this->db->next_record()) {
        $current_user = new person;
        $current_user->read_db_record($this->db);

        // generate new random password
        $password = "";
        $pwSource = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?";
        srand((float) microtime() * 1000000);
        for ($i = 0; $i < 8; $i++) {
          $password.= substr($pwSource, rand(0, strlen($pwSource)), 1);
        }
        $current_user->set_value('password', db_esc(crypt(trim($password), trim($password))));
        $current_user->save();
        $mail = mail($email, "New Alloc password request", "Your new temporary password: ".$password, "From: Alloc <alloc-admin@cyber.com.au>");
        $error = "New password sent to: ".$email;
        define("LOGIN_ERROR",$error);
      } else {
        $error = "Invalid username or email address.";
        define("LOGIN_ERROR",$error);
      }

      // Login attempt
    } else 


if (isset($login) && isset($username) && $username != "" && isset($password) && $password != "") {
      $q = sprintf("SELECT * FROM %s WHERE username = '%s'", $this->database_table, db_esc($username));
      $this->db->query($q);
      $this->db->next_record();
      $salt = $this->db->f("password");
      $q = sprintf("SELECT * FROM %s WHERE username = '%s' AND password = '%s'", $this->database_table, db_esc($username), db_esc(crypt(trim($password), $salt)));
      $this->db->query($q);
      if ($this->db->next_record()) {
        if ($this->db->f("personActive")) {
          $uid = $this->db->f("personID");
          $this->auth["perm"] = $this->db->f("perms");
          $current_user = new person;
          $current_user->read_db_record($this->db);
          $sess->register ("current_user");
          $query = "UPDATE person SET lastLoginDate='".date("Y-m-d H:i:s")."' WHERE personID=".$this->db->f("personID");
          $this->db->query($query);
          return $uid;
        } else {
          $error = "Account deactivated. Please see an alloc admin.";
          define("LOGIN_ERROR",$error);
        }
      } else {
        $error = "Invalid username or password.";
        define("LOGIN_ERROR",$error);
      }
    }
  }

?>
