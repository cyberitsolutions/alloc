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
  public $data_table = "timeSheetItem";
  public $display_field_name = "description";
  public $key_field = "timeSheetItemID";
  public $data_fields = array("timeSheetID"
                             ,"dateTimeSheetItem"
                             ,"timeSheetItemDuration"
                             ,"timeSheetItemDurationUnitID"
                             ,"rate"
                             ,"personID"
                             ,"description"
                             ,"comment"
                             ,"taskID"
                             ,"multiplier"
                             ,"commentPrivate"
                             );

  function save() {
  
    $timeSheet = new timeSheet;
    $timeSheet->set_id($this->get_value("timeSheetID"));
    $timeSheet->select();
    $timeSheet->load_pay_info();
    list($amount_used,$amount_allocated) = $timeSheet->get_amount_allocated();

    $_POST["timeSheetItem_commentPrivate"] and $this->set_value("commentPrivate", 1);
    $this->set_value("comment",rtrim($this->get_value("comment")));

    $amount_of_item = $this->calculate_item_charge($timeSheet->pay_info["customerBilledDollars"]);
    if ($amount_allocated && ($amount_of_item + $amount_used) > $amount_allocated) {
      return "Adding this Time Sheet Item would exceed the amount allocated on the Pre-paid invoice.<br>Time Sheet Item not saved.";
    } 

    if ($_POST["timeSheetItem_taskID"]) {
      $selectedTask = new task();
      $selectedTask->set_id($_POST["timeSheetItem_taskID"]);
      $selectedTask->select();
      $taskName = $selectedTask->get_task_name();

      if (!$selectedTask->get_value("dateActualStart")) {
        $selectedTask->set_value("dateActualStart", $this->get_value("dateTimeSheetItem"));
      }
      if ($selectedTask->get_value("taskSubStatus") == "notstarted") {
        $selectedTask->set_value("taskSubStatus", "inprogress");
      }
      $selectedTask->skip_perms_check = true;
      $selectedTask->save();
    }

    $this->set_value("description", $taskName);

    parent::save();

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

    // Update the related invoiceItem
    $q = sprintf("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$this->get_value("timeSheetID"));
    $db->query($q);
    $row = $db->row();
    if ($row) {
      $ii = new invoiceItem;
      $ii->set_id($row["invoiceItemID"]);
      $ii->select();
      $ii->add_timeSheet($row["invoiceID"],$this->get_value("timeSheetID"));  // will update the existing invoice item
    }
  } 

  function calculate_item_charge($rate=false) {
    $rate === false and $rate = $this->get_value("rate");
    return $rate * $this->get_value("timeSheetItemDuration") * $this->get_value("multiplier");
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
