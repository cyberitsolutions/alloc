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

class history extends db_entity {
  public $data_table = "history";

  # Display the x most recent, but keep the 2x most recent
  # once we hit 3x, delete back down to 2x
  public $max_to_display = 40;

  public $key_field = historyID;
  public $data_fields = array("the_time"
                             ,"the_place"
                             ,"the_args"
                             ,"the_label"
                             ,"personID"
                             );


  function get_history_query($order="") {
    $current_user = &singleton("current_user");
    if (is_object($current_user)) {
      $db = new db_alloc();
      $query = prepare("SELECT *, historyID AS value, the_label AS label 
                         FROM history 
                        WHERE personID = %d 
                     GROUP BY the_label 
                     ORDER BY the_time %s
                        LIMIT %d"
                      ,$current_user->get_id(),$order,$this->max_to_display);
    } 
    return $query;
  }

  // Returns an array of files which 
  // will be used to determine whether 
  // or not to save a history entry. 

  function get_ignored_files() {
    $ignored_files = array();
    $query = $this->get_history_query("ASC");
    if ($query) { 
      $db = new db_alloc();
      $db->query($query);
      while ($db->next_record()) {
        $ignored_files[] = end(explode("/", $db->f("the_place").$db->f("the_args")));
      }
    }
    $ignored_files[] = "index.php";
    $ignored_files[] = "home.php";
    $ignored_files[] = "taskList.php";
    $ignored_files[] = "projectList.php";
    $ignored_files[] = "timeSheetList.php";
    $ignored_files[] = "menu.php";
    $ignored_files[] = "clientList.php";
    $ignored_files[] = "itemLoan.php";
    $ignored_files[] = "personList.php";
    $ignored_files[] = "reminderList.php";
    $ignored_files[] = "search.php";
    $ignored_files[] = "person.php";

    return $ignored_files;
  }

  function get_history_label($SCRIPT_NAME, $qs) {

    // Save the history record LABEL with the most descriptive label
    // possible, using the class variable->display_field_name

    $db = new db_alloc();
    $script_name_array = explode("/", $SCRIPT_NAME);

    $file = end($script_name_array);
    $CLASS_NAME = str_replace(".php", "", $file);                         // File name without .php extension
    $dir = $script_name_array[sizeof($script_name_array) - 2];            // Directory that file is in
    $qs = preg_replace("[^\?]", "", $qs);                                 // Nuke the leading question mark of the query string attached to end of url eg: ?tfID=23&anal=true

    // We can only get a descriptive history entry if there is a xxxID 
    // on the url, that way we can get the specific records label.
    if ($qs) {
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

          if (class_exists($CLASS_NAME) && $ID) {
            $newClass = new $CLASS_NAME;
            $display_field = $newClass->display_field_name;

            if (is_object($newClass->key_field) && $newClass->key_field->get_name() == $KEY_FIELD) {
              // The primary key for this db table is the same as 
              // our KEY_FIELD var which was extracted from url.
              $query = prepare("SELECT * FROM %s WHERE %s = %d", $CLASS_NAME, $KEY_FIELD, $ID);
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
              return ucwords($CLASS_NAME).$rtn;
            }
          }
        }
      }
    }
    return false;
  }

  function save_history() {
    $current_user = &singleton("current_user");
    global $TPL;

    // Delete old items if they have too many.
    if (!is_object($current_user) || !$current_user->get_id()) {
      return;
    }
    $db = new db_alloc();
    $query = prepare("SELECT count(*) AS total FROM history WHERE personID = %d",$current_user->get_id());
    $db->query($query);
    $row = $db->row();
    if ($row["total"] >= (3*$this->max_to_display)) {
      // Can only use DELETE FROM .. ORDER BY syntax from mysql >= 4.0
      if (version_compare($db->get_db_version(),"4.0",">=")) {
        $query  = prepare("DELETE FROM history WHERE personID = %d ORDER BY the_time LIMIT %d",$current_user->get_id(),$this->max_to_display, (2*$this->max_to_display));
        $db->query($query);
      }
    }

    $ignored_files = $this->get_ignored_files();
    if ($_SERVER["QUERY_STRING"]) {
      $qs = $this->strip_get_session($_SERVER["QUERY_STRING"]);
      $qs = preg_replace("[&$]", "", $qs);
      $qs = preg_replace("[^&]", "", $qs);
      $qs = preg_replace("[^\?]", "", $qs);
    }

    $file = end(explode("/", $_SERVER["SCRIPT_NAME"]))."?".$qs;

    if (is_object($current_user) && !in_array($file, $ignored_files)
        && !$_GET["historyID"] && !$_POST["historyID"] && $the_label = $this->get_history_label($_SERVER["SCRIPT_NAME"], $qs)) {

      $the_place = basename($_SERVER["SCRIPT_NAME"]);

      foreach($TPL as $k => $v) {
        $key = basename($this->strip_get_session($v));
        $key = preg_replace("[&$]", "", $key);
        $key = preg_replace("[\?$]", "", $key);
        $arr[$key] = $k;
      }

      if ($arr[$the_place]) {
        $this->set_value("personID", $current_user->get_id());
        $this->set_value("the_place", $arr[$the_place]);
        $this->set_value("the_args", $qs);
        $this->set_value("the_label", $the_label);
        $this->set_value("the_time", date("Y-m-d H:i:s"));
        $this->save();
      }
    }
  }

  function strip_get_session($str="") {
    return (string)preg_replace("/sess=[A-Za-z0-9]{32}/","",$str);
  }


}


?>
