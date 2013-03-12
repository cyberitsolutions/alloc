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

class timeSheetItem extends db_entity {
  public $data_table = "timeSheetItem";
  public $display_field_name = "description";
  public $key_field = "timeSheetItemID";
  public $data_fields = array("timeSheetID"
                             ,"dateTimeSheetItem"
                             ,"timeSheetItemDuration"
                             ,"timeSheetItemDurationUnitID"
                             ,"rate" => array("type"=>"money")
                             ,"personID"
                             ,"description"
                             ,"comment"
                             ,"taskID"
                             ,"multiplier"
                             ,"commentPrivate"
                             ,"emailUID"
                             ,"emailMessageID"
                             ,"timeSheetItemCreatedTime"
                             ,"timeSheetItemCreatedUser"
                             ,"timeSheetItemModifiedTime"
                             ,"timeSheetItemModifiedUser"
                             );

  function save() {
    $current_user = &singleton("current_user");
    $timeSheet = new timeSheet();
    $timeSheet->set_id($this->get_value("timeSheetID"));
    $timeSheet->select();

    $timeSheet->load_pay_info();
    list($amount_used,$amount_allocated) = $timeSheet->get_amount_allocated("%mo");

    $this->currency = $timeSheet->get_value("currencyTypeID");

    $this->set_value("comment",rtrim($this->get_value("comment")));

    $amount_of_item = $this->calculate_item_charge($timeSheet->get_value("currencyTypeID"),$timeSheet->get_value("customerBilledDollars"));
    if ($amount_allocated && ($amount_of_item + $amount_used) > $amount_allocated) {
      alloc_error("Adding this Time Sheet Item would exceed the amount allocated on the Pre-paid invoice. Time Sheet Item not saved.");
    } 

    // If unit is changed via CLI
    if ($this->get_value("timeSheetItemDurationUnitID") && $timeSheet->pay_info["project_rateUnitID"]
    && $timeSheet->pay_info["project_rateUnitID"] != $this->get_value("timeSheetItemDurationUnitID") && !$timeSheet->can_edit_rate()) {
      alloc_error("Not permitted to edit time sheet item unit.");
    }

    if (!$this->get_value("timeSheetItemDurationUnitID") && $timeSheet->pay_info["project_rateUnitID"]) {
      $this->set_value("timeSheetItemDurationUnitID", $timeSheet->pay_info["project_rateUnitID"]);
    }

    // Last ditch perm checking - useful for the CLI
    if (!is_object($timeSheet) || !$timeSheet->get_id()) {
      alloc_error("Unknown time sheet.");
    }
    if ($timeSheet->get_value("status") != "edit" && !$this->skip_tsi_status_check) {
      alloc_error("Time sheet is not at status edit");
    }
    if (!$this->is_owner()) {
      alloc_error("Time sheet is not editable for you.");
    }

    $rtn = parent::save();

    $db = new db_alloc();

    // Update the related invoiceItem
    $q = prepare("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$this->get_value("timeSheetID"));
    $db->query($q);
    $row = $db->row();
    if ($row) {
      $ii = new invoiceItem();
      $ii->set_id($row["invoiceItemID"]);
      $ii->select();
      $ii->add_timeSheet($row["invoiceID"],$this->get_value("timeSheetID"));  // will update the existing invoice item
    }
    return $rtn;
  } 

  function parse_time_string($str) {
    preg_match("/^"
              ."(\d\d\d\d\-\d\d?\-\d\d?\s+)?"   # date
              ."([\d\.]+)?"          # duration
              ."\s*"
              ."(hours|hour|hrs|hr|days|day|weeks|week|months|month|fixed)?" # unit
              ."\s*"
              ."(x\s*[\d\.]+)?"     # multiplier eg: x 1.5
              ."\s*"
              ."(\d+)?"             # task id
              ."\s*"
              ."(.*)"               # comment
              ."\s*"
              #."(private)?"        # whether the comment is private 
              ."$/i",$str,$m);

    $rtn["date"] = trim($m[1]) or $rtn["date"] = date("Y-m-d");
    $rtn["duration"] = $m[2];
    $rtn["unit"] = $m[3];
    $rtn["multiplier"] = str_replace(array("x","X"," "),"",$m[4]) or $rtn["multiplier"] = 1;
    $rtn["taskID"] = $m[5];
    $rtn["comment"] = $m[6];
    //$rtn["private"] = $m[7];

    // use the first letter of the unit for the lookup
    $tu = array("h"=>1,"d"=>2,"w"=>3,"m"=>4,"f"=>5);
    $rtn["unit"] = $tu[$rtn["unit"][0]] or $rtn["unit"] = 1;

    // change 2010/10/27 to 2010-10-27
    $rtn["date"] = str_replace("/","-",$rtn["date"]);

    return $rtn;
  }

  function calculate_item_charge($currency,$rate=0) {
    return page::money($currency, $rate * $this->get_value("timeSheetItemDuration") * $this->get_value("multiplier"), "%mo");
  }

  function delete() {
    $timeSheetID = $this->get_value("timeSheetID");
    parent::delete();

    $db = new db_alloc();
    $q = prepare("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$timeSheetID);
    $db->query($q);
    $row = $db->row();
    if ($row) {
      $ii = new invoiceItem();
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
    $personID and $personID_sql = prepare(" AND personID = %d", $personID);

    $q = prepare("SELECT DISTINCT dateTimeSheetItem, personID
                    FROM timeSheetItem 
                   WHERE dateTimeSheetItem > '%s'
                      ".$personID_sql."
                GROUP BY dateTimeSheetItem,personID
                 ",$dateTimeSheetItem);

    $db = new db_alloc();
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

    // Convert all the monies into native currency
    foreach ($rows_dollars as $id => $arr) {
      foreach ($arr as $r) {
        $alex[$id] += exchangeRate::convert($r["currency"],$r["amount"]);
      }
    }

    // Get the averages for each
    foreach ((array)$alex as $id => $sum) {
      $rtn_dollars[$id] = $sum / $how_many_fortnights[$id];
    }

    return array($rtn,$rtn_dollars);
  }

  function is_owner() {
    if ($this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet();
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $timeSheet->select();
      return $timeSheet->is_owner();
    }
  }
  
  function get_list_filter($filter=array()) {

    // If timeSheetID is an array
    if ($filter["timeSheetID"] && is_array($filter["timeSheetID"])) {
      $timeSheetIDs = $filter["timeSheetID"];

    // Else
    } else if ($filter["timeSheetID"] && is_numeric($filter["timeSheetID"])) {
      $timeSheetIDs[] = $filter["timeSheetID"];
    }

    if (is_array($timeSheetIDs) && count($timeSheetIDs)) {
      $sql[] = prepare("(timeSheetItem.timeSheetID IN (%s))",$timeSheetIDs);
    } 

    if ($filter["projectID"]) {
      $sql[] = prepare("(timeSheet.projectID = %d)",$filter["projectID"]);
    } 

    if ($filter["taskID"]) {
      $sql[] = prepare("(timeSheetItem.taskID = %d)",$filter["taskID"]);
    } 

    if ($filter["date"]) {
      in_array($filter["dateComparator"],array("=","!=",">",">=","<","<=")) or $filter["dateComparator"] = '=';
      $sql[] = prepare("(timeSheetItem.dateTimeSheetItem ".$filter["dateComparator"]." '%s')",$filter["date"]);
    } 

    if ($filter["personID"]) {
      $sql[] = prepare("(timeSheetItem.personID = %d)",$filter["personID"]);
    } 

    if ($filter["timeSheetItemID"]) {
      $sql[] = prepare("(timeSheetItem.timeSheetItemID = %d)",$filter["timeSheetItemID"]);
    }

    if ($filter["comment"]) {
      $sql[] = prepare("(timeSheetItem.comment LIKE '%%%s%%')",$filter["comment"]);
    }

    if ($filter["tfID"]) {
      $sql[] = prepare("(timeSheet.recipient_tfID = %d)", $filter["tfID"]);
    }

    return $sql;
  }

  function get_list($_FORM) {
    /*
     * This is the definitive method of getting a list of timeSheetItems that need a sophisticated level of filtering
     *
     */
   
    global $TPL;
    $filter = timeSheetItem::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";
    $_FORM["return"] or $_FORM["return"] = "html";

    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT * FROM timeSheetItem
       LEFT JOIN timeSheet ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
                 ".$filter."
        ORDER BY timeSheet.timeSheetID,dateTimeSheetItem asc";
    $debug and print "Query: ".$q;
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->next_record()) {
      $print = true;
      $t = new timeSheet();
      $t->read_db_record($db);
      
      $tsi = new timeSheetItem();
      $tsi->read_db_record($db);
      $tsi->currency = $t->get_value("currencyTypeID");

      $row["secondsBilled"] = $row["hoursBilled"] = $row["timeLimit"] = $row["limitWarning"] = ""; # set these for the CLI
      if ($tsi->get_value("taskID")) {
        $task = $tsi->get_foreign_object('task');
        $row["secondsBilled"] = $task->get_time_billed();
        $row["hoursBilled"] = sprintf("%0.2f",$row["secondsBilled"] / 60 / 60);
        $task->get_value('timeLimit') && $row["hoursBilled"] > $task->get_value('timeLimit') and $row["limitWarning"] = 'Exceeds Limit!';
        $row["timeLimit"] = $task->get_value("timeLimit");
      }
      $row["rate"] = $tsi->get_value("rate",DST_HTML_DISPLAY);
      $row["worth"] = page::money($tsi->currency, $row["rate"] * $tsi->get_value("multiplier") * $tsi->get_value("timeSheetItemDuration"),"%m");

      $rows[$row["timeSheetItemID"]] = $row;
    }

    if ($print && $_FORM["return"] == "array") {
      return $rows;
    }
  }

  function get_list_vars() {
    return array("return"                   => "[MANDATORY] eg: array | html | dropdown_options"
                ,"timeSheetID"              => "Show items for a particular time sheet"
                ,"projectID"                => "Show items for a particular project"
                ,"taskID"                   => "Show items for a particular task"
                ,"date"                     => "Show items for a particular date"
                ,"personID"                 => "Show items for a particular person"
                ,"comment"                  => "Show items that have a comment like eg: *uick brown fox jump*"
                );
  }

  #function get_averages_past_fortnight($personID=false) {
   # $dateTimeSheetItem = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y")));
    // DON'T ERASE THIS!! This way will divide by the number of individual days worked
    #$rows = $this->get_averages($dateTimeSheetItem, $personID, "/ COUNT(DISTINCT dateTimeSheetItem)");

    // This will just get the sum of hours worked for the last two weeks
    #$rows = $this->get_averages($dateTimeSheetItem, $personID);

    #return $rows;
  #}


  function get_averages($dateTimeSheetItem, $personID=false, $divisor="", $endDate=null) {

    $personID and $personID_sql = prepare(" AND timeSheetItem.personID = %d", $personID);
    $endDate and $endDate_sql = prepare(" AND timeSheetItem.dateTimeSheetItem <= '%s'",$endDate);

    $q = prepare("SELECT personID
                       , SUM(timeSheetItemDuration*timeUnitSeconds) ".$divisor." AS avg
                    FROM timeSheetItem 
               LEFT JOIN timeUnit ON timeUnitID = timeSheetItemDurationUnitID 
                   WHERE dateTimeSheetItem > '%s'
                      ".$personID_sql."
                      ".$endDate_sql."
                GROUP BY personID
                 ", $dateTimeSheetItem);

    $db = new db_alloc();
    $db->query($q);
    $rows = array();
    while ($db->next_record()) {
      $rows[$db->f("personID")] = $db->f("avg")/3600;
    }

    //Calculate the dollar values
    $q = prepare("SELECT (rate * POW(10, -currencyType.numberToBasic) * timeSheetItemDuration * multiplier) as amount
                       , timeSheet.currencyTypeID as currency 
                       , timeSheetItem.*
                    FROM timeSheetItem 
               LEFT JOIN timeSheet on timeSheetItem.timeSheetID = timeSheet.timeSheetID
               LEFT JOIN currencyType ON timeSheet.currencyTypeID = currencyType.currencyTypeID
                WHERE dateTimeSheetItem > '%s'
                      ".$personID_sql."
                      ".$endDate_sql
                , $dateTimeSheetItem);
    $db->query($q);
    $rows_dollars = array();
    while($row = $db->row()) {
      $tsi = new timeSheetItem();
      $tsi->read_db_record($db);
      $rows_dollars[$row["personID"]][] = $row;
    }
    return array($rows,$rows_dollars);
  }

  function get_timeSheetItemComments($taskID="",$starred=false) {
    // Init
    $rows = array();

    if ($taskID) {
      $where = prepare("timeSheetItem.taskID = %d",$taskID);
    } else if ($starred) {
      $current_user = &singleton("current_user");
      $timeSheetItemIDs = array();
      foreach ((array)$current_user->prefs["stars"]["timeSheetItem"] as $k=>$v) {
        $timeSheetItemIDs[] = $k;
      }
      $where = prepare("(timeSheetItem.timeSheetItemID in (%s))",$timeSheetItemIDs);
    }
    
    $where or $where = " 1 ";

    // Get list of comments from timeSheetItem table
    $query = prepare("SELECT timeSheetID
                           , timeSheetItemID
                           , dateTimeSheetItem AS date
                           , comment
                           , personID
                           , taskID
                           , timeSheetItemDuration as duration
                           , timeSheetItemCreatedTime
                        FROM timeSheetItem
                       WHERE ".$where." AND (commentPrivate != 1 OR commentPrivate IS NULL)
                         AND emailUID is NULL
                         AND emailMessageID is NULL
                    ORDER BY dateTimeSheetItem,timeSheetItemID
                     ");

    $db = new db_alloc();
    $db->query($query);
    while ($row = $db->row()) {
      $rows[] = $row;
    }

    is_array($rows) or $rows = array();
    return $rows;
  }


  function get_total_hours_worked_per_day($personID,$start=null,$end=null) {
    $current_user =& singleton("current_user");

    $personID or $personID = $current_user->get_id();
    $start    or $start    = date("Y-m-d",mktime()-(60*60*24*28));
    $end      or $end      = date("Y-m-d");

    $q = prepare("SELECT dateTimeSheetItem, sum(timeSheetItemDuration*timeUnitSeconds) / 3600 AS hours
                    FROM timeSheetItem
               LEFT JOIN timeUnit ON timeUnitID = timeSheetItemDurationUnitID
                   WHERE personID = %d
                     AND dateTimeSheetItem >= '%s'
                     AND dateTimeSheetItem <= '%s'
                GROUP BY dateTimeSheetItem"
                ,$personID, $start, $end);
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $info[$row["dateTimeSheetItem"]] = $row;
    }

    $num_days_back = (format_date("U",$end) - format_date("U",$start)) /60/60/24;
    $x = 1;
    while ($x<=$num_days_back) {
      $d = date("Y-m-d",format_date("U",$end) - (60*60*24*($num_days_back-$x)));
      $points[] = array($d, sprintf("%d",$info[$d]["hours"]));
      $x++;
    }

    return $points;
  }

  function get_total_hours_worked_per_month($personID,$start=null,$end=null) {
    $current_user =& singleton("current_user");

    $personID or $personID = $current_user->get_id();
    $start    or $start    = date("Y-m-d",mktime()-(60*60*24*28));
    $end      or $end      = date("Y-m-d");

    $q = prepare("SELECT CONCAT(YEAR(dateTimeSheetItem),'-',MONTH(dateTimeSheetItem)) AS dateTimeSheetItem
                       , sum(timeSheetItemDuration*timeUnitSeconds) / 3600 AS hours
                    FROM timeSheetItem
               LEFT JOIN timeUnit ON timeUnitID = timeSheetItemDurationUnitID
                   WHERE personID = %d
                     AND dateTimeSheetItem >= '%s'
                     AND dateTimeSheetItem <= '%s'
                GROUP BY YEAR(dateTimeSheetItem), MONTH(dateTimeSheetItem)"
                ,$personID, $start, $end);
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $f = explode("-",$row["dateTimeSheetItem"]);
      $info[sprintf("%4d-%02d",$f[0],$f[1])] = $row; // the %02d is just to make sure the months are consistently zero padded
    }

    $s = format_date("U",$start);
    $e = format_date("U",$end);
    $s_months = (date("Y",$s) * 12) + date("m",$s);
    $e_months = (date("Y",$e) * 12) + date("m",$e);

    $num_months_back = $e_months - $s_months;
    $x = 0;
    while ($x<=$num_months_back) {
      $time = mktime(0,0,0,date("m",$s)+$x,1,date("Y",$s));
      $d = date("Y-m",$time);
      $points[] = array($d, sprintf("%d",$info[$d]["hours"]));
      $x++;
    }
    
    return $points;
  }

}



?>
