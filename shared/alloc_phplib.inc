<?php
class db_alloc extends DB_Sql {
  function db_alloc() {
    $this->Host = ALLOC_DB_HOST;
    $this->Database = ALLOC_DB_NAME;
    $this->User = ALLOC_DB_USER;
    $this->Password = ALLOC_DB_PASS;
  }
} 

class alloc_Session_sql extends CT_Sql {

  var $database_table = "active_sessions";
  var $database_class = "db_alloc";
  var $database_lock_semaphore = "";
  var $encoding_mode = "base64";
  var $allow_cache = "no";
} 

class alloc_Session extends Session {

  var $classname = "alloc_Session";
  var $cookiename = "";
  var $magic = "peanutbuttersandwhiches";

  ## ID seed
  #var $mode           = "cookie";          ## We propagate session IDs with cookies
  var $mode = "get";
  
  ## We propagate session IDs with variables in URL
  var $fallback_mode = "get";
  var $lifetime = 0;
  ## 0 = do session cookies, else minutes
  var $database_class = "db_alloc";
  
  ## Which database to connect...
  var $database_table = "active_sessions";
  ## and find our session data in this table.
  var $that_class = "alloc_Session_sql";

  ## Name of data storage container
  var $allowcache = "no";

  // Add a session ID to a URL, unless we have been called from the automatic email cron job
  function email_url($url) {

    // This is a bit of a hack.  When we are sending emails we don't want to put session ID's on the URL.
    // However, we have a session set when sending email, but no authentication has occurred
    // So if we don't have an $auth object we add a session ID
    global $auth, $SERVER_NAME;
    if (isset($auth)) {
      $url = $this->url($url);

    } else {
      // but we do want the servername to be added when sending emails as there is no host
      // to attach the relative address to
      $url = "http://$SERVER_NAME$url";
    }
    return $url;
  }
}

class alloc_User extends User {
  var $classname = "alloc_User";
  var $magic = "Abracadabra";
  var $database_class = "db_alloc";
  var $database_table = "active_sessions";
  var $that_class = "alloc_Session_sql";
}

class alloc_Auth extends Auth {
  var $classname = "alloc_Auth";
  var $lifetime = 360;
  var $database_class = "db_alloc";
  var $database_table = "person";
  var $clientno;
  function auth_loginform() {
    global $sess;
      include(ALLOC_MOD_DIR."/shared/loginform.ihtml");
  } 

  function auth_validatelogin() {
    global $error, $new_pass, $login, $email, $username, $password, $current_user, $sess;

    // provides access for loginform.ihtml
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
      } else {
        $error = "Invalid username or email address.";
      }

      // Login attempt
    } else if (isset($login) && isset($username) && $username != "" && isset($password) && $password != "") {
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
        }
      } else {
        $error = "Invalid username or password.";
      }
    }
  }
}

class alloc_Default_Auth extends alloc_Auth {
  var $classname = "alloc_Default_Auth";
  var $nobody = true;
}

class alloc_Challenge_Auth extends Auth {
  var $classname = "alloc_Challenge_Auth";
  var $lifetime = 1;
  var $magic = "Simsalabim";
  var $database_class = "db_alloc";
  var $database_table = "person";

  function auth_loginform() {
    global $sess;
    global $challenge;
      $challenge = md5(uniqid($this->magic));
      $sess->register ("challenge");
      include("crloginform.ihtml");
  } 


  function auth_validatelogin() {
    global $username, $password, $challenge, $response;
    $this->auth["uname"] = $username;
    ## This provides access for "loginform.ihtml"
    $this->db->query(sprintf("select uid,perms,password "."from %s where username = '%s'", $this->database_table, addslashes($username)));
    while ($this->db->next_record()) {
      $uid = $this->db->f("uid");
      $perm = $this->db->f("perms");
      $pass = $this->db->f("password");
    }
    $exspected_response = md5("$username:$pass:$challenge");

    ## True when JS is disabled
    if ($response == "") {
      if ($password != $pass) {
        return false;
      } else {
        $this->auth["perm"] = $perm;
        return $uid;
      }
    }
    ## Response is set, JS is enabled
    if ($exspected_response != $response) {
      return false;
    } else {
      $this->auth["perm"] = $perm;
      return $uid;
    }
  }
}

class alloc_Perm extends Perm {
  var $classname = "alloc_Perm";
  var $permissions = array("god"      =>1, 
                           "admin"    =>2,  // Administration staff
                           "manage"   =>4,  // Managers
                           "employee" =>8   // Employees
                           );
  function perm_invalid($does_have, $must_have) {
    global $perm, $auth, $sess;
      include(ALLOC_MOD_DIR."/shared/perminvalid.ihtml");
  }
}

?>
