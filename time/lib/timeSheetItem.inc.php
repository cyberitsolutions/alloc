<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

class timeSheetItem extends db_entity {
  var $data_table = "timeSheetItem";
  var $display_field_name = "description";

  function timeSheetItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("timeSheetItemID");
    $this->data_fields = array("timeSheetID"=>new db_text_field("timeSheetID")
                               , "dateTimeSheetItem"=>new db_text_field("dateTimeSheetItem")
                               , "timeSheetItemDuration"=>new db_text_field("timeSheetItemDuration")
                               , "timeSheetItemDurationUnitID"=>new db_text_field("timeSheetItemDurationUnitID")
                               , "rate"=>new db_text_field("rate")
                               , "personID"=>new db_text_field("personID")
                               , "description"=>new db_text_field("description")
                               , "comment"=>new db_text_field("comment")
                               , "taskID"=>new db_text_field("taskID")
                               , "commentPrivate"=>new db_text_field("commentPrivate")
      );
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
                       , SUM(timeSheetItemDuration*rate) as dollars
                    FROM timeSheetItem 
               LEFT JOIN timeUnit ON timeUnitID = timeSheetItemDurationUnitID 
                   WHERE dateTimeSheetItem > '%s'
                      %s
                GROUP BY personID
                 ",$divisor, $dateTimeSheetItem, $personID_sql);

    $db = new db_alloc;
    $db->query($q);
    $rows = array();
    $rows_dollars = array();
    while ($db->next_record()) {
      $rows[$db->f("personID")] = $db->f("avg")/3600;
      $rows_dollars[$db->f("personID")] = $db->f("dollars");
    }
    return array($rows,$rows_dollars);
  }

}



?>
