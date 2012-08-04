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

class session {
  
  var $key;          # the unique key for the session 
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
  function __construct($key="") {
    global $TPL;
    $this->key           = $key or $this->key = $_COOKIE["alloc_cookie"] or $this->key = $_GET["sess"] or $this->key = $_REQUEST["sessID"];
    $TPL["sessID"]       = $_GET["sess"];
    $this->db            = new db_alloc();
    #$this->session_life  = (5); 
    $this->session_life  = (config::get_config_item("allocSessionMinutes")*60); 
    $this->session_life < 1 and $this->session_life = 10000; // just in case.
    $this->session_data  = $this->UnEncode($this->GetSessionData());
    $this->mode          = $this->Get("session_mode"); 

    if ($this->Expired()) {
      $this->Destroy();
    }
    return $this;
  }


  // Call this in a login page to start session 
  function Start($row,$nuke_prev_sessions=true) {
    $this->key = md5($row["personID"]."mix it up#@!".md5(mktime().md5(microtime())));
    $this->Put("session_started", mktime());
    if ($nuke_prev_sessions && config::get_config_item("singleSession")) {
      $this->db->query("DELETE FROM sess WHERE personID = %d",$row["personID"]);
    }
    $this->db->query("INSERT INTO sess (sessID,sessData,personID) VALUES ('%s','%s',%d)"
                             ,$this->key, $this->Encode($this->session_data), $row["personID"]);
    $this->Put("username" ,strtolower($row["username"]));
    $this->Put("perms" ,$row["perms"]);
    $this->Put("personID" ,$row["personID"]);
  }

  // Test whether session has started 
  function Started() {
    if ($this->Get("session_started") && !$this->Expired())
      return true;
  }

  function Save() {
    if ($this->Expired()) {
      $this->Destroy();
    } else if ($this->Started()) {
      $this->Put("session_started",mktime());
      $this->db->query("UPDATE sess SET sessData = '%s' WHERE sessID = '%s'"
                  , $this->Encode($this->session_data), $this->key);
    }
  } 

  function Destroy() {
    if ($this->Started() && $this->key) {
      $this->db->query("DELETE FROM sess WHERE sessID = '%s'",$this->key);
    }
    $this->DestroyCookie();
    $this->key = "";
  }

  function Put($name,$value) {
    $this->session_data[$name] = $value;
  }

  function Get($name) {
    return $this->session_data[$name];
  }

  function GetKey() {
    return $this->key;
  }

  function MakeCookie() {

    // Attempt to unset the test cookie
    #SetCookie("alloc_test_cookie",FALSE,0,"/","");

    // Set the session cookie
    $rtn = SetCookie("alloc_cookie",$this->key,0,"/","");
    if (!$rtn) {
      $this->mode = "get";
    } else if (!isset($_COOKIE["alloc_cookie"])) {
      $_COOKIE["alloc_cookie"] = $this->key;
    }
  } 

  function DestroyCookie() {
    SetCookie("alloc_cookie",FALSE,0,"/","");
    unset($_COOKIE["alloc_cookie"]);
  }

  function SetTestCookie($val="alloc_test_cookie") {
    SetCookie("alloc_test_cookie",$val,0,"/","");
  }

  function TestCookie() {
    return $_COOKIE["alloc_test_cookie"];
  }

  function GetUrl($url="") {
    return $this->url($url);
  }

  function url($url="") {
    $url = preg_replace("/[&?]+$/", "", $url);

    if ($this->mode == "get") {
      if (!strpos($url, "sess=") && $this->key) {
        $extra = "sess=".$this->key."&";
      }
    }

    if (strpos($url, "?")) {
      $url.= "&";
    } else {
      $url.= "?";
    }

    return $url.$extra;
  }

  function UseGet() {
    $this->mode = "get";
    $this->DestroyCookie();
    $this->Put("session_mode",$this->mode);
  }
 
  function UseCookie() {
    $this->mode = "cookie";
    $this->MakeCookie();
    $this->Put("session_mode",$this->mode);
  }


  // * * * * * * * * * * * * * * * * *//
  //                                  //
  //         Private Methods          //
  //                                  //
  // * * * * * * * * * * * * * * * * *// 

  // Fetches data given a key
  function GetSessionData() {
    if ($this->key) {
      $row = $this->db->qr("SELECT sessData FROM sess WHERE sessID = '%s'", $this->key);
      return $row["sessData"];
    }
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
    alloc_error($msg);
  }
}


?>
