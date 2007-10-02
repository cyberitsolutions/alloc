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

// Class to handle all emails that alloc sends
// 
// Will log emails sent, and will not attempt to send email when the server is a dev boxes
// 
class alloc_email {
  
  // If URL has any of these strings in it then the email won't be sent.
  #var $no_email_urls = array("alloc_dev");
  var $no_email_urls = array();

  // If alloc is running on any of these boxes then no emails will be sent!
  var $no_email_hosts = array("garlic.office.cyber.com.au"
                             ,"spectrum.lancewood.net"
			     ,"peach.office.cyber.com.au"
			     ,"peach"
                             ,"alloc_dev"
                             );
                             #,"mint.lancewood.net"
                             #,"mint"

  // Set to true to skip host and url checking
  var $ignore_no_email_hosts = false; 
  var $ignore_no_email_urls = false; 

  // Actual email variables
  var $to_address = "";
  var $header = array();
  var $subject = "";
  var $body = ""; 

  function alloc_email($to_address="",$subject="",$message="",$message_type="",$header=array()) {
    $to_address   and $this->to_address   = $to_address;
    $subject      and $this->subject      = $subject;
    $message      and $this->message      = $message;
    $message_type and $this->message_type = $message_type;
    $header       and $this->header       = $header;
  }

  function send($to_address="",$subject="",$message="",$message_type="",$header=array()) {
    $to_address        and $this->to_address   = $to_address;
    $subject           and $this->subject      = $subject;
    $message           and $this->message      = $message;
    $message_type      and $this->message_type = $message_type;

    if (is_array($this->header) && is_array($header)) {
      $this->header = array_merge($this->header,$header);
    } else if (is_array($header)) {
      $this->header = $header;
    }

    if (!$this->header["From"]) {
      $this->header["From"] = ALLOC_DEFAULT_FROM_ADDRESS;
    }

    if (!$this->header["Content-Type"]) {
      $this->header["Content-Type"] = "text/plain; charset=utf-8";
    }

    $this->subject = "[allocPSA] ".$this->subject;

    if ($this->is_valid_url()) {
    
      if (is_array($this->header)) {
        foreach ($this->header as $k => $v) {
          $h.= $br.$k.": ".$v;
          $br = "\r\n";
        }
      }

      $result = mail($this->to_address, $this->subject, $this->message, $h);
      if ($result) {
        $this->log();
        return true;
      } 
    }
    return false;
  }

  function set_from($personID="") {
    if ($personID) {
      $person = new person;
      $person->set_id($personID);
      $person->select();
      if ($person->get_value("emailAddress")) {
        $this->header["From"] = $person->get_username(1)." <".$person->get_value("emailAddress").">";
      }
    } 
    $this->header["From"] or $this->header["From"] = ALLOC_DEFAULT_FROM_ADDRESS;
  }

  function set_reply_to($email=false) {
    $email or $email = ALLOC_DEFAULT_FROM_ADDRESS;
    $this->header["Reply-To"] or $this->header["Reply-To"] = $email;
  }

  function set_message_id($hash="") {
    $hash and $hash = ".".$hash;
    list($usec, $sec) = explode(" ", microtime());
    $time = $sec.$usec;
    $time = base_convert($time,10,36);
    $rand = md5(microtime().getmypid().md5(microtime()));
    $rand = base_convert($rand,16,36);
    $bits = explode("@",ALLOC_DEFAULT_FROM_ADDRESS);
    $host = str_replace(">","",$bits[1]);
    $this->header["Message-ID"] = "<".$time.".".$rand.$hash."@".$host.">";
  }

  function is_valid_url() {

    // Validate against particular hosts
    in_array($_SERVER["SERVER_NAME"], $this->no_email_hosts) and $dont_send = true;
    $this->ignore_no_email_hosts and $dont_send = false;

    // Validate against particular bits in the url
    foreach ($this->no_email_urls as $url) {
      preg_match("/".$url."/",$_SERVER["SCRIPT_FILENAME"]) and $dont_send = true;
    }
    $this->ignore_no_email_urls and $dont_send = false;

    // Invert return
    return !$dont_send;
  }

  function log($message="") {
    global $current_user;
    $sentEmailLog = new sentEmailLog();
    $sentEmailLog->set_value("sentEmailTo",$this->to_address);
    $sentEmailLog->set_value("sentEmailSubject",$this->subject);
    $sentEmailLog->set_value("sentEmailBody",$this->message);

    if (is_array($this->header)) {
      foreach ($this->header as $k => $v) {
        $h.= $br.$k.": ".$v;
        $br = "\r\n";
      }
    }
    $sentEmailLog->set_value("sentEmailHeader",$h);
    $sentEmailLog->set_value("sentEmailType",$this->message_type);
    $sentEmailLog->save();
  }

}


?>
