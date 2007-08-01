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

class alloc_email_receive {

  var $host;
  var $port;
  var $username;
  var $password;
  var $protocol;
  var $lockfile;
  var $locked = false;
  var $mbox;
  var $connection;
  var $mail_headers;
  var $mail_structure;
  var $msg_num;
  
  function alloc_email_receive($info,$lockfile) {
    $this->host     = $info["host"];
    $this->port     = $info["port"];
    $this->username = $info["username"];
    $this->password = $info["password"];
    $this->protocol = $info["protocol"] or $this->protocol = "imap";
    $this->lockfile = $lockfile;


    // Nuke lock files that are more that 10 min old 
    
    if (file_exists($this->lockfile) && (time() - filemtime($this->lockfile)) > 600) {
      unlink($this->lockfile);
    } 

    if (file_exists($this->lockfile)) {
      $this->locked = true;
      #die("Email lock file found: ".$this->lockfile." Unable to continue!");

    } else if (is_dir(dirname($this->lockfile)) && is_writeable(dirname($this->lockfile))) {
      $fh = fopen($this->lockfile,"w");
      fputs($fh,date("r"));
      fclose($fh);
    }
  }
  
  function open_mailbox($folder="") {
    if (!$this->locked) {
      $connect_string = '{'.$this->host.':'.$this->port.'/'.$this->protocol.'/notls/norsh}'.$folder;
      $this->connection = imap_open($connect_string, $this->username, $this->password);
      
    } else {
      unset($this->connection);
    }

    if (!$this->connection) {
      echo "<pre>".print_r(imap_errors(),1)."</pre>"; 
      echo "<pre>".print_r(imap_alerts(),1)."</pre>"; 
    }
  }

  function check_mail() {
    if ($this->connection) {
      $this->mailbox_info = imap_check($this->connection);
    } else { 
      unset($this->mailbox_info);
    }
  }

  function set_msg($x) {
    $this->msg_num = $x;
  }

  function get_msg_header($num=0) {
    $num or $num = $this->msg_num;
    $num and $this->mail_headers = imap_headerinfo($this->connection, $num);
    return $this->mail_headers;
  }

  function load_structure() {
    $this->mail_structure = imap_fetchstructure($this->connection,$this->msg_num);
  }

  function save_email($file) {
    $header = imap_fetchheader($this->connection,$this->msg_num,FT_PREFETCHTEXT);
    $body = imap_body($this->connection,$this->msg_num);
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

  function forward($address,$subject) {
    $header = imap_fetchheader($this->connection,$this->msg_num,FT_PREFETCHTEXT);
    $body = imap_body($this->connection,$this->msg_num);
    mail($address,$subject,$header.$body,"From: ".get_default_from_address());
  }

  function close() {
    if (file_exists($this->lockfile)) {
      unlink($this->lockfile);  
    }
    if ($this->connection) {
      #imap_close($this->connection);
      imap_close($this->connection,CL_EXPUNGE); // expunge messages marked for deletion
    }
  }

  function delete($x=0) {
    #return;
    $x or $x = $this->msg_num;
    if ($this->connection) {
      imap_delete($this->connection, $x);
    }
  }

  function expunge() {
    imap_expunge($this->connection);
  }

  function get_hashes($headers=false) {

    $headers or $headers = $this->mail_headers;
    $keys = array();

    if (config::get_config_item("allocEmailKeyMethod") == "subject") {

      if (preg_match("/\{Key:[A-Za-z0-9]{8}\}/i",$headers->subject,$m)) {
        $key = $m[0];
        $key = str_ireplace(array("{Key:","}"),"",$key);
        $key and $keys[] = $key;
      }

    } else if (config::get_config_item("allocEmailKeyMethod") == "headers") {

      $str = $headers->in_reply_to." ".$headers->references;

      preg_match_all("/([A-Za-z0-9]{8})@/",$str,$m);

      if (is_array($m[1])) {
        $temp = array_flip($m[1]);// unique pls
        foreach ($temp as $k => $v) {
          $keys[] = $k;
        }
      } 
    }

    return $keys;
  }


}



?>
