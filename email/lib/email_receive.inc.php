<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

class alloc_email_receive {

  var $host;
  var $port;
  var $username;
  var $password;
  var $protocol;
  var $lockfile;
  var $mbox;
  var $connection;
  var $mail_headers;
  var $mail_structure;
  
  function alloc_email_receive($info,$lockfile) {
    $this->host     = $info["host"];
    $this->port     = $info["port"];
    $this->username = $info["username"];
    $this->password = $info["password"];
    $this->protocol = $info["protocol"] or $this->protocol = "imap";
    $this->lockfile = $lockfile;


    // Nuke lock files that are more that 30 min old 
    if (file_exists($this->lockfile) && (time() - filemtime($this->lockfile)) > 1800) {
      $this->unlock();
    } 

    if (file_exists($this->lockfile)) {
      die("Mailbox is locked. Remove ".$this->lockfile." to unlock.");
    } else {
      $this->lock();
    }
  
  }
  
  function open_mailbox($folder="",$ops=OP_HALFOPEN) {
    $connect_string = '{'.$this->host.':'.$this->port.'/'.$this->protocol.config::get_config_item("allocEmailExtra").'}';
    $this->connect_string = $connect_string;
    $this->connection = imap_open($connect_string, $this->username, $this->password, $ops) or die("Unable to access mail folder(1).");
    $list = imap_list($this->connection, $connect_string, "*");
    if (!is_array($list) || !count($list)) { // || !in_array($connect_string.$folder,$list)) {
      $this->unlock();
      imap_close($this->connection); 
      die("Unable to access mail folder(2).");
    } else {
      $connect_string.= $folder;
      $rtn = imap_reopen($this->connection, $connect_string);
      if (!$rtn) {
        imap_close($this->connection); 
        die("Unable to access mail folder(3).");
      }
    }

    if (!$this->connection) {
      echo "<pre>".print_r(imap_errors(),1)."</pre>"; 
      echo "<pre>".print_r(imap_alerts(),1)."</pre>"; 
    }
  }

  function get_num_emails() {
    if (!$this->mailbox_info) {
      $this->check_mail();
    }
    if (is_object($this->mailbox_info)) {
      return $this->mailbox_info->messages;
    }
  }

  function get_num_new_emails() {
    if (!$this->mailbox_info) {
      $this->check_mail();
    }
    if (is_object($this->mailbox_info)) {
      return $this->mailbox_info->unseen;
    }
  }

  function check_mail() {
    if ($this->connection) {
      $this->mailbox_info = imap_status($this->connection, $this->connect_string, SA_ALL);
    } else { 
      unset($this->mailbox_info);
    }
  }

  function get_new_email_msg_uids() {
    return imap_search($this->connection,"UNSEEN",SE_UID);
  }

  function get_all_email_msg_uids() {
    return imap_search($this->connection,"ALL",SE_UID);
  }

  function get_emails_UIDs_search($str) {
    return imap_search($this->connection,$str, SE_UID);
  }

  function set_msg($x) {
    $this->msg_uid = $x;
  }

  function get_msg_header($uid=0) {
    $uid or $uid = $this->msg_uid;
    $uid and $this->mail_headers = imap_rfc822_parse_headers(imap_fetchheader($this->connection, $uid, FT_UID));
    return $this->mail_headers;
  }

  function load_structure() {
    $this->mail_structure = imap_fetchstructure($this->connection,$this->msg_uid,FT_UID);
  }

  function get_raw_email_by_msg_uid($msg_uid) {
    $result = imap_fetch_overview($this->connection,$msg_uid,FT_UID);
    // only view emails that *have* been seen before otherwise 
    // we might view an email before it has been downloaded by
    // receiveEmail.php
    if (is_array($result) && $result[0]->seen) { 
      $header = imap_fetchheader($this->connection,$msg_uid,FT_PREFETCHTEXT+FT_UID);
      $body = imap_body($this->connection,$msg_uid,FT_UID);
      return array($header,$body);
    }
    return array("","");
  }

  function save_email($file) {
    $header = imap_fetchheader($this->connection,$this->msg_uid,FT_PREFETCHTEXT+FT_UID);
    $header and $this->mail_headers = imap_rfc822_parse_headers($header);
    $body = imap_body($this->connection,$this->msg_uid,FT_UID);
    $fh = fopen($file,"w+");
    fputs($fh, $header.$body);
    fclose($fh);

    $mime = new mime_parser;
    $mime->decode_bodies = 1;
    $mime->ignore_syntax_errors = 1;
    $parameters = array('File'=>$file, "SaveBody"=>dirname($file));
    $mime->Decode($parameters, $decoded);
    #echo "<pre>".print_r($decoded,1)."</pre>";

    // remove email.eml file and remove file called 1, which should just be the textual message body
    $dir = dirname($file);
    file_exists($file) && unlink($file);
    #file_exists($dir.DIRECTORY_SEPARATOR."1") && unlink($dir.DIRECTORY_SEPARATOR."1"); 

    if (is_dir($dir)) {
      $handle = opendir($dir);
      clearstatcache();
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
          $num_files++;
          clearstatcache();
        }
      }
    }
    // Nuke dir if empty
    if (!$num_files) {
      rmdir($dir);
    }

    return $decoded;
  }

  function mark_seen() {
    imap_setflag_full($this->connection, $this->msg_uid, "\\SEEN", FT_UID); // this doesn't work!
    $body = imap_body($this->connection,$this->msg_uid,FT_UID); // this seems to force it to be marked seen
  }

  function forward($address,$subject) {
    $header = imap_fetchheader($this->connection,$this->msg_uid,FT_UID);
    $body = imap_body($this->connection,$this->msg_uid,FT_UID);

    $email = new alloc_email();
    $email->set_headers($header);

    $orig_subject = $email->get_header("subject");
  
    // Nuke certain headers from the email
    $email->del_header("to");
    $email->del_header("subject");
    $email->del_header("cc");
    $email->del_header("bcc");

    $email->set_subject($subject." [".trim($orig_subject)."]");    
    $email->set_to_address($address);
    $email->set_message_type("orphan");
    $email->set_body($body);
    $email->send(false);
  }

  function lock() {
    if (is_dir(dirname($this->lockfile)) && is_writeable(dirname($this->lockfile))) {
      $fh = fopen($this->lockfile,"w");
      fputs($fh,date("r"));
      fclose($fh);
    }
  }

  function unlock() {
    if (file_exists($this->lockfile)) {
      unlink($this->lockfile);  
    }
  }

  function close() {
    $this->unlock();
    if ($this->connection) {
      #imap_close($this->connection);
      imap_close($this->connection,CL_EXPUNGE); // expunge messages marked for deletion
    }
  }

  function delete($x=0) {
    #return;
    $x or $x = $this->msg_uid;
    if ($this->connection) {
      imap_delete($this->connection, $x, FT_UID);
    }
  }

  function expunge() {
    imap_expunge($this->connection);
  }

  function get_hashes($headers=false) {
    $headers or $headers = $this->mail_headers;
    $keys = array();

    if (preg_match("/\{Key:[A-Za-z0-9]{8}\}/i",$headers->subject,$m)) {
      $key = $m[0];
      $key = str_replace(array("{Key:","}"),"",$key);
      $key and $keys[] = $key;
    }

    $str = $headers->in_reply_to." ".$headers->references;

    preg_match_all("/([A-Za-z0-9]{8})@/",$str,$m);

    if (is_array($m[1])) {
      $temp = array_flip($m[1]);// unique pls
      foreach ($temp as $k => $v) {
        $keys[] = $k;
      }
    }

    return array_unique((array)$keys);
  }


}



?>
