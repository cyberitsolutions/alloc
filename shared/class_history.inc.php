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

class history extends db_entity {
  var $data_table = "history";
  var $total_to_display = 30;

  function history() {
    $this->db_entity();
    $this->key_field = new db_text_field("historyID");
    $this->data_fields = array("the_time"=>new db_text_field("the_time")
                               , "the_place"=>new db_text_field("the_place")
                               , "the_label"=>new db_text_field("the_label")
                               , "personID"=>new db_text_field("personID")
      );
  }

  // Get $db object which is set to 
  // correct spot in db for that user
  // to show $this->total_to_display

  function get_history_db() {
    global $current_user;
    $db = new db_alloc;
    $TOTAL = $this->total_to_display;   // Total to display

    if (is_object($current_user)) {
      $query = sprintf("SELECT * FROM history WHERE personID = %d 
						  GROUP BY the_label ORDER BY the_time", $current_user->get_id());
      $db->query($query);

      if (($db->num_rows() > $TOTAL) && ($db->num_rows() != 0)) {
        $start = $db->num_rows() - $TOTAL;
        $db->seek($start);
      }
    } else {
      $db = false;
    }
    return $db;
  }

  // Returns an array of files which 
  // will be used to determine whether 
  // or not to save a history entry. 

  function get_ignored_files() {
    $ignored_files = array();
    $db = $this->get_history_db();
    while (is_object($db) && $db->next_record()) {
      $ignored_files[] = end(explode("/", $db->f("the_place")));
    }
    return $ignored_files;
  }

  function get_history_label($SCRIPT_NAME, $qs) {

    // Save the history record LABEL with the most descriptive label
    // possible, using the class variable->display_field_name

    $db = new db_alloc;
    $script_name_array = explode("/", $SCRIPT_NAME);

    $file = end($script_name_array);
    // File name without .php extension
    $CLASS_NAME = str_replace(".php", "", $file);
    // Directory that file is in
    $dir = $script_name_array[sizeof($script_name_array) - 2];
    // Nuke the leading question mark of the query string attached 
    // to end of url eg: ?tfID=23&anal=true
    $qs = preg_replace("[^\?]", "", $qs);

    if ($qs) {
      // We can only get a descriptive history entry if there is a xxxID 
      // on the url, that way we can get the specific records label.
      $qs_array = explode("&", $qs);

      foreach($qs_array as $query_pair) {
        // Break up url query string into key/value pairs.

        if (preg_match("/$CLASS_NAME/", $query_pair)) {
          // Look for a key like eg: transactionID so in that case it'd 
          // use the class transaction.

          $key_and_value = explode("=", $query_pair);
          // Break key/value up into $KEY_FIELD and $ID
          $ID = $key_and_value[1];
          $KEY_FIELD = $key_and_value[0];

          if (class_exists($CLASS_NAME)) {
            $newClass = new $CLASS_NAME;
            $display_field = $newClass->display_field_name;

            if ($newClass->key_field->get_name() == $KEY_FIELD) {
              // The primary key for this db table is the same as 
              // our KEY_FIELD var which was extracted from url.
              $query = sprintf("select * from %s where %s = %d", $CLASS_NAME, $KEY_FIELD, $ID);
              $db->query($query);
              $db->next_record();
              // return that particular classes _default_ display field
              // eg: for the table project, it would be projectName
              $rtn = $db->f($display_field);

              // But if our search for a descriptive text label failed 
              // because the above search returned a number try again
              // to get a description from the next table

              // Get a new id and key field name and table name 
              // Strip the trailing 'ID' from the , to get new class name 
              $next_class_name = preg_replace("/ID$/", "", $display_field);


              if (is_numeric($rtn) && class_exists($next_class_name)) {
                $next_class = new $next_class_name;
                if ($display_field == $next_class->key_field->get_name()) {
                  // If the display field was eg: tfID and that equals the key field of this table
                  $next_class->set_id($rtn);
                  $next_class->select();
                  $rtn = $next_class->get_value($next_class->display_field_name);
                } else {
                  $rtn = $ID;
                }
              }
              $rtn = ": ".$rtn;
              return addslashes(ucwords($CLASS_NAME).$rtn);
            }
          }
        }
      }
    }
    return false;
  }

}


?>
