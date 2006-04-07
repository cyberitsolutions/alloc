<?php
class Session {
  
  var $key;          # the unique key for the session 
  var $key2;         # a per web browser key incase someone gets first key
  var $db;           # database object 
  var $session_data; # assoc array which holds all session data
  var $session_life; # number of seconds the session is alive for
  var $mode;         # whether to use get or cookies


  // * * * * * * * * * * * * * * * * *//
  //                                  //
  //         Public Methods           //
  //                                  //
  // * * * * * * * * * * * * * * * * *// 



  // Constructor
  function Session() {
    $this->key           = $_COOKIE["alloc_cookie"] or $this->key = $_GET["sess"];
    $this->key2          = md5("ung!uessibbble".$_SERVER['HTTP_USER_AGENT']);
    $this->db            = new db_alloc;
    $this->session_life  = (60*60*24); 
    $this->session_data  = $this->UnEncode($this->GetSessionData());
    $this->mode          = $this->Get("session_mode"); 
  }

  // Singleton 
  function GetSession() {
    static $s;
    is_object($s) or $s = new Session();
    return $s;
  } 

  // Call this in a login page to start session 
  function Start($personID) {
    $this->key = md5($personID."mix it up#@!".md5(mktime()));
    $this->Put("key2", $this->key2);
    $this->Put("session_started", mktime());
    $this->db->query("DELETE FROM sess WHERE personID = %s",$personID);
    $this->db->query("INSERT INTO sess (sessID,sessData,personID) VALUES (%s,%s,%s)"
                             ,$this->key, $this->Encode($this->session_data), $personID);
  }

  // Test whether session has started 
  function Started() {
    if ($this->Get("session_started") && $this->Get("key2") == $this->key2)
      return true;
  }

  // Save $this->session_data to $this->session_file
  function Save() {
    if ($this->Expired()) {
      $this->Destroy();
    } else if ($this->Started() && $this->SessionHasChanged()) {
      $this->db->query("UPDATE sess SET sessData = %s WHERE sessID = %s"
                  , $this->Encode($this->session_data), $this->key);
    }
  } 

  // end session
  function Destroy() {
    if ($this->Started() && $this->key) {
      $this->db->query("DELETE FROM sess WHERE sessID = %s",$this->key);
    }
    $this->DestroyCookie();
    $this->key = "";
  }

  // Save sessions data
  function Put($name,$value) {
    $this->session_data[$name] = $value;
  }

  // Fetch session data
  function Get($name) {
    return $this->session_data[$name];
  }

  // Get unique key
  function GetKey() {
    return $this->key;
  }

  // Use cookies
  function MakeCookie() {
    // Set a cookie.
    $rtn = SetCookie("alloc_cookie",$this->key,0,"/",$_SERVER["HTTP_HOST"]);
    if (!$rtn) {
      echo "Unable to set cookie";
      $this->mode = "get";
    } else if (!isset($_COOKIE["alloc_cookie"])) {
      $_COOKIE["alloc_cookie"] = $this->key;
    }
  } 

  // Destroy cookies
  function DestroyCookie() {
    if ($this->mode == "cookie") {
      SetCookie("alloc_cookie",false,time()-3600,"/",$_SERVER["HTTP_HOST"]);
    }
    unset($_COOKIE["alloc_cookie"]);
  }

  // Wrapper
  function GetUrl($url="") {
    return $this->url($url);
  }

  // Wrapper to return url with a session id on them
  function url($url="") {
   $url = ereg_replace("[&?]+$", "", $url);

    if ($this->mode == "get") {
       (strpos($url, "sess=") == false) && $this->key and $extra = "sess=".$this->key."&";
    }

    $url.= (strpos($url, "?") != false ? "&" : "?").$extra;

    return $url;
  }

  function UseGet() {
    $this->mode = "get";
    $this->Put("session_mode",$this->mode);
  }
 
  function UseCookie() {
    $this->mode = "cookie";
    $this->MakeCookie();
    $this->Put("session_mode",$this->mode);
  }


  // Add a session ID to a URL, unless we have been called from the automatic email cron job
  function email_url($url) {

    // This is a bit of a hack.  When we are sending emails we don't want to put session ID's on the URL.
    // However, we have a session set when sending email, but no authentication has occurred
    // So if we don't have an $current_user object we add a session ID
    $sess = Session::GetSession();

    global  $SERVER_NAME;
    if ($sess->Started()) { 
      $url = $this->url($url);
  
    } else {
      // but we do want the servername to be added when sending emails as there is no host
      // to attach the relative address to
      $url = "http://$SERVER_NAME$url";
    } 
    return $url;
  } 


  // * * * * * * * * * * * * * * * * *//
  //                                  //
  //         Private Methods          //
  //                                  //
  // * * * * * * * * * * * * * * * * *// 

  // Fetches data given a key
  function GetSessionData() {
    if ($this->key) {
      $row = $this->db->qr("SELECT sessData FROM sess WHERE sessID = %s", $this->key);
      return $row["sessData"];
    }
  }

  // see if session has changed
  function SessionHasChanged(){
    $data_in_db = $this->GetSessionData();
    $data_in_object = $this->Encode($this->session_data); 
    return $data_in_db != $data_in_object;
  }

  // if $this->session_life seconds have passed then session has expired
  function Expired() {
    if ($this->Get("session_started") && (mktime() > ($this->Get("session_started")+$this->session_life))) {
      return true;
    }
  }
  // add encryption for session_data here 
  function Encode($data){
    return serialize($data);
  }

  // and add unencryption for session_data here
  function UnEncode($data){
    return unserialize($data);
  }
  
  // errors fix me 
  function Error($msg) {
    die($msg);
  }
}


?>
