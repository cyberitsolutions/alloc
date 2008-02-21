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

class timeSheetItem extends db_entity {
  var $data_table = "timeSheetItem";
  var $display_field_name = "description";

  function timeSheetItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("timeSheetItemID");
    $this->data_fields = array("timeSheetID"=>new db_field("timeSheetID")
                             , "dateTimeSheetItem"=>new db_field("dateTimeSheetItem")
                             , "timeSheetItemDuration"=>new db_field("timeSheetItemDuration")
                             , "timeSheetItemDurationUnitID"=>new db_field("timeSheetItemDurationUnitID")
                             , "rate"=>new db_field("rate")
                             , "personID"=>new db_field("personID")
                             , "description"=>new db_field("description")
                             , "comment"=>new db_field("comment")
                             , "taskID"=>new db_field("taskID")
                             , "multiplier"=> new db_field("multiplier")
                             , "commentPrivate"=>new db_field("commentPrivate")
      );
  }

  function save() {
  
    $timeSheet = new timeSheet;
    $timeSheet->set_id($this->get_value("timeSheetID"));
    $timeSheet->select();
    $timeSheet->load_pay_info();
    $total = $timeSheet->pay_info["total_customerBilledDollars"] or $total = $timeSheet->pay_info["total_dollars"];

    if ($timeSheet->pay_info["customerBilledDollars"] > 0) {
      $total+= $this->get_value("timeSheetItemDuration") * $timeSheet->pay_info["customerBilledDollars"];
    } else {
      $total+= $this->get_value("timeSheetItemDuration") * $this->get_value("rate");
    }

    $limit = $timeSheet->get_amount_allocated();

    if ($limit && $total > $limit) {
      return "Adding this Time Sheet Item would exceed the amount allocated for this Time Sheet.";
      exit;
    }

    parent::save();

    $db = new db_alloc();
    if ($_POST["timeSheetItem_taskID"] != 0 && $_POST["timeSheetItem_taskID"]) {
      $db->query("select taskName,dateActualStart from task where taskID = %d",$_POST["timeSheetItem_taskID"]);
      $db->next_record();
      $taskName = $db->f("taskName");
      if (!$db->f("dateActualStart")) {
        $q = sprintf("UPDATE task SET dateActualStart = '%s' WHERE taskID = %d",$this->get_value("dateTimeSheetItem"),$_POST["timeSheetItem_taskID"]);
        $db->query($q);
      }
    }
    $this->set_value("description", $taskName);

    $db = new db_alloc();
    $db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) as count
                        FROM timeSheetItem WHERE timeSheetID=%d ", $this->get_value("timeSheetID")));
    $db->next_record();
    $timeSheet = new timeSheet;
    $timeSheet->set_id($this->get_value("timeSheetID"));
    $timeSheet->select();
    $timeSheet->set_value("dateFrom", $db->f("minDate"));
    $timeSheet->set_value("dateTo", $db->f("maxDate"));
    $status2 = $timeSheet->save();

    // ALEX REMEMBER invoiceItem PERMS!!!
    #$q = sprintf("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$this->get_value("timeSheetID"));
    #$db->query($q);
    #$row = $db->row();
    #if ($row) {
    #  $ii = new invoiceItem;
    #  $ii->set_id($row["invoiceItemID"]);
    #  $ii->select();
    #  $ii->add_timeSheet($row["invoiceID"],$this->get_value("timeSheetID"));  // will update the existing invoice item
    #}
  } 

  function calculate_item_charge() {
    $multipliers = config::get_config_item("timeSheetMultipliers");
    return sprintf($this->get_value("rate") * $this->get_value("timeSheetItemDuration") * $multipliers[$this->get_value("multiplier")]['multiplier']);
  }

  function delete() {

    $timeSheetID = $this->get_value("timeSheetID");

    parent::delete();

    $db = new db_alloc();
    $db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) as count
                        FROM timeSheetItem WHERE timeSheetID=%d ", $this->get_value("timeSheetID")));
    $db->next_record();
    $timeSheet = new timeSheet;
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();
    $timeSheet->set_value("dateFrom", $db->f("minDate"));
    $timeSheet->set_value("dateTo", $db->f("maxDate"));
    $status2 = $timeSheet->save();

    $q = sprintf("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$timeSheetID);
    $db->query($q);
    $row = $db->row();
    if ($row) {
      $ii = new invoiceItem;
      $ii->set_id($row["invoiceItemID"]);
      $ii->select();
      $ii->add_timeSheet($row["invoiceID"],$timeSheetID);  // will update the existing invoice item
    }
  }

  function get_fortnightly_average($personID=false) {

    // Need an array of the past years fortnights 
    $x = 0;
    while ($x < 365) {
      if ($x % 14 == 0) {
        $fortnight++;
      }
      $fortnights[date("Y-m-d",mktime(0,0,0,date("m"),date("d")-365+$x,date("Y")))] = $fortnight;
      $x++;
    }

    $dateTimeSheetItem = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-365,date("Y")));
    $personID and $personID_sql = sprintf(" AND personID = '%d'", $personID);

    $q = sprintf("SELECT DISTINCT dateTimeSheetItem, personID
                    FROM timeSheetItem 
                   WHERE dateTimeSheetItem > '%s'
                      %s
                GROUP BY dateTimeSheetItem,personID
                 ",$dateTimeSheetItem,$personID_sql);

    $db = new db_alloc;
    $db->query($q);
    while ($db->next_record()) {
      
      if (!$done[$db->f("personID")][$fortnights[$db->f("dateTimeSheetItem")]]) {
        $how_many_fortnights[$db->f("personID")]++;
        $done[$db->f("personID")][$fortnights[$db->f("dateTimeSheetItem")]] = true;
      }
    }

    $rtn = array();
    list($rows,$rows_dollars) = $this->get_averages($dateTimeSheetItem,$personID);
    foreach ($rows as $id => $avg) {
      $rtn[$id] = $avg / $how_many_fortnights[$id];
      #echo "<br>".$id." ".$how_many_fortnights[$id];
    }
    foreach ($rows_dollars as $id => $dollars) {
      $rtn_dollars[$id] = $dollars / $how_many_fortnights[$id];
    }
    return array($rtn,$rtn_dollars);
  }

  function is_owner() {
    if ($this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet;
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $timeSheet->select();
      return $timeSheet->is_owner();
    }
  }
  
  #function get_averages_past_fortnight($personID=false) {
   # $dateTimeSheetItem = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y")));
    // DON'T ERASE THIS!! This way will divide by the number of individual days worked
    #$rows = $this->get_averages($dateTimeSheetItem, $personID, "/ COUNT(DISTINCT dateTimeSheetItem)");

    // This will just get the sum of hours worked for the last two weeks
    #$rows = $this->get_averages($dateTimeSheetItem, $personID);

    #return $rows;
  #}


  function get_averages($dateTimeSheetItem, $personID=false, $divisor="") {

    $personID and $personID_sql = sprintf(" AND personID = '%d'", $personID);

    $q = sprintf("SELECT personID
                       , SUM(timeSheetItemDuration*timeUnitSeconds) %s AS avg
                    FROM timeSheetItem 
               LEFT JOIN timeUnit ON timeUnitID = timeSheetItemDurationUnitID 
                   WHERE dateTimeSheetItem > '%s'
                      %s
                GROUP BY personID
                 ",$divisor, $dateTimeSheetItem, $personID_sql);

    $db = new db_alloc;
    $db->query($q);
    $rows = array();
    while ($db->next_record()) {
      $rows[$db->f("personID")] = $db->f("avg")/3600;
    }

    //Calculate the dollar values
    $q = sprintf("SELECT * FROM timeSheetItem WHERE dateTimeSheetItem > '%s' %s", $dateTimeSheetItem, $personID_sql);
    $db->query($q);
    $rows_dollars = array();
    while($db->next_record()) {
      $tsi = new timeSheetItem();
      $tsi->read_db_record($db);
      $rows_dollars[$db->f("personID")] += $tsi->calculate_item_charge();
    }
    return array($rows,$rows_dollars);
  }

  function get_timeSheetItemComments($taskID="") {
    // Init
    $rows = array();

    // Get list of comments from timeSheetItem table
    $query = sprintf("SELECT timeSheetID, dateTimeSheetItem AS date, comment, personID
                        FROM timeSheetItem
                       WHERE timeSheetItem.taskID = %d AND (commentPrivate != 1 OR commentPrivate IS NULL)
                    ORDER BY dateTimeSheetItem,timeSheetItemID
                     ",$taskID);

    $db = new db_alloc;
    $db->query($query);
    while ($row = $db->row()) {
      $rows[] = $row;
    }

    is_array($rows) or $rows = array();
    return $rows;
  }

}



?>
