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

class comment extends db_entity {
  var $data_table = "comment";

  function comment() {
    $this->db_entity();
    $this->key_field = new db_text_field("commentID");
    $this->data_fields = array("commentType"=>new db_text_field("commentType"),
                               "commentLinkID"=>new db_text_field("commentLinkID"), "commentModifiedTime"=>new db_text_field("commentModifiedTime"), "commentModifiedUser"=>new db_text_field("commentModifiedUser"), "comment"=>new db_text_field("comment"));
  }

  // set the modified time to now
  function set_modified_time() {
    $this->set_value("commentModifiedTime", date("Y-m-d H:i:s"));
  }

  // return just the date of the comment without the time
  function get_modified_date() {
    return substr($this->get_value("commentModifiedTime"), 0, 10);
  }


  function send_email($recipient, $subject) {
    global $current_user;

    // New email object wrapper takes care of logging etc.
    $email = new alloc_email;
    $email->set_from($current_user->get_id());

    // REMOVE ME!!
    $email->ignore_no_email_urls = true;

    #$person_commentModifiedUser = new person;
    #$person_commentModifiedUser->set_id($this->get_value("commentModifiedUser"));
    #$person_commentModifiedUser->select();
    #$message.= "\n\nNew comments by ".$person_commentModifiedUser->get_username(1)." ".$this->get_value("commentModifiedTime");

    $message = "\n".wordwrap($this->get_value("comment"));

    // Convert plain old recipient address blah@cyber.com.au to Alex Lance <blah@cyber.com.au>
    if ($recipient["firstName"] && $recipient["surname"] && $recipient["emailAddress"]) {
      $recipient["emailAddress"] = $recipient["firstName"]." ".$recipient["surname"]." <".$recipient["emailAddress"].">";
    } else if ($recipient["fullName"] && $recipient["emailAddress"]) {
      $recipient["emailAddress"] = $recipient["fullName"]." <".$recipient["emailAddress"].">";
    }

    if ($recipient["emailAddress"]) {
      return $email->send($recipient["emailAddress"], $subject, $message);
    }
  }
}



?>
