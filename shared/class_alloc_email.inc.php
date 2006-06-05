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
  var $no_email_urls = array("alloc_dev");

  // If alloc is running on any of these boxes then no emails will be sent!
  var $no_email_hosts = array("garlic.office.cyber.com.au"
                             ,"spectrum.lancewood.net"
                             ,"mint.lancewood.net"
                             ,"mint"
                             ,"peach.office.cyber.com.au"
                             );

  // Set to true to skip host and url checking
  var $ignore_no_email_hosts = false; 
  var $ignore_no_email_urls = false; 

  // Actual email variables
  var $to_address = "";
  var $header = "";
  var $subject = "";
  var $body = ""; 

  // Initializer
  function alloc_email($to_address="",$subject="",$message="",$header="",$logfile="") {

    $to_address  and $this->to_address = $to_address;
    $subject     and $this->subject    = $subject;
    $message     and $this->message    = $message;
    $header      and $this->header     = $header;
    $this->header or $this->header     = "From: allocPSA ".ALLOC_DEFAULT_FROM_ADDRESS;
    $this->logfile                     = ALLOC_LOG_DIR."alloc_email.log";
  }

  // Send and log the email
  function send($to_address="",$subject="",$message="",$header="") {
    $to_address  and $this->to_address = $to_address;
    $subject     and $this->subject    = $subject;
    $message     and $this->message    = $message;
    $header      and $this->header     = $header;

    if (!$this->header || !preg_match("/\\\@/",$this->header)) {
      $this->header = "From: allocPSA ".ALLOC_DEFAULT_FROM_ADDRESS;
    }
    
    $this->subject                     = "allocPSA ".$this->subject;

    if (!$this->is_valid_to_address()) {
      $this->log("Not sending: '".stripslashes($this->subject)."' to ".$this->to_address." -> To Address is bad!");

    } else if (!$this->is_valid_url()) {
      $this->log("Not sending: '".stripslashes($this->subject)."' to ".$this->to_address." -> Invalid url. SERVER_NAME: ".$_SERVER["SERVER_NAME"]." and SCRIPT_FILENAME: ".$_SERVER["SCRIPT_FILENAME"]);

    } else {
      $this->log("Sending: ".$this->subject." to ".$this->to_address);
      return mail($this->to_address, stripslashes($this->subject), stripslashes($this->message), $this->header);
    }
  }

  function set_from($personID="") {
    $person = new person;
    $person->set_id($personID);
    $person->select();
    if ($person->get_value("emailAddress")) {
      $this->header = "From: ".$person->get_username(1)." <".$person->get_value("emailAddress").">";
    } else {
      $this->header = "From: ".$person->get_username(1)." <".ALLOC_DEFAULT_FROM_ADDRESS.">";
    }
  }

  // Will return true if $this->to_address is true
  function is_valid_to_address() {
    // TODO
    if ($this->to_address) {
      return true;
    }
  }

  // Will return true if the requested URL is ok to send from
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


  // Log
  function log($message="") {

    $fp = @fopen($this->logfile, "a+");

    if (!$this->logfile || !is_resource($fp)) {
      die("Unable to write to logfile: ".$this->logfile);
    } else {
      fputs($fp, date("Y-m-d H:i:s ").$message."\n");
      fclose($fp);
    }
  }

}


?>
