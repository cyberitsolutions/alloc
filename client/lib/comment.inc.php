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
    $this->key_field = new db_field("commentID");
    $this->data_fields = array("commentType"=>new db_field("commentType")
                              ,"commentLinkID"=>new db_field("commentLinkID")
                              ,"commentModifiedTime"=>new db_field("commentModifiedTime")
                              ,"commentModifiedUser"=>new db_field("commentModifiedUser")
                              ,"comment"=>new db_field("comment")
                              );
  }

  // set the modified time to now
  function set_modified_time() {
    $this->set_value("commentModifiedTime", date("Y-m-d H:i:s"));
  }

  // return just the date of the comment without the time
  function get_modified_date() {
    return substr($this->get_value("commentModifiedTime"), 0, 10);
  }

  function get_comments($commentType="",$commentLinkID="") {
    if ($commentType && $commentLinkID) {
      $q = sprintf("SELECT commentID, commentLinkID, commentModifiedTime AS date, comment, commentModifiedUser AS personID 
                      FROM comment 
                     WHERE commentType = '%s' AND commentLinkID = %d 
                  ORDER BY commentModifiedTime"
                  ,$commentType, $commentLinkID);
      $db = new db_alloc;
      $db->query($q);
      while ($row = $db->row()) {
        $rows[] = $row;
      }
      return $rows;
    }
  }


}



?>
