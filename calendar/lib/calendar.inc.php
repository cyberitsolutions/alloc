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


class calendar {
  var $person;
  var $week_start;
  var $weeks_to_display;
  var $days_of_week = array();
  var $rtp;
  var $first_date;
  var $last_date;
  var $db;
  var $first_day_of_week;


  function __construct($week_start=1, $weeks_to_display=4) {
    $this->db = new db_alloc();
    $this->first_day_of_week = config::get_config_item("calendarFirstDay");
    $this->set_cal_date_range($week_start, $weeks_to_display);
    $this->days_of_week = $this->get_days_of_week_array($this->first_day_of_week);
  }

  function set_cal_person($personID) {
    $this->person = new person();
    $this->person->set_id($personID);
    $this->person->select();
  }

  function set_cal_date_range($week_start, $weeks_to_display) {
    $this->week_start = $week_start;
    $this->weeks_to_display = $weeks_to_display;

    // Wind the date forward till we find the starting day of week
    while (date("D", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y"))) != $this->first_day_of_week) {
      $i++;
    }
    $fd = mktime(date("H"),date("i"),date("s"),date("m"),date("d")-($this->week_start*7)+($i-7),date("Y"));
    
    /// Set the first and last date on the page 
    $this->first_date = date("Y-m-d",$fd); 
    $this->last_date = date("Y-m-d", mktime(date("H",$fd)
                                           ,date("i",$fd)
                                           ,date("s",$fd)
                                           ,date("m",$fd)
                                           ,date("d",$fd)+(($this->weeks_to_display*7)-1)
                                           ,date("Y",$fd)));
  }

  function get_cal_reminders() {

    // Get persons reminders
    $query = prepare("SELECT * 
                        FROM reminder
                        JOIN reminderRecipient ON reminderRecipient.reminderID = reminder.reminderID
                       WHERE personID = %d
                         AND (reminderRecuringInterval = 'No' OR (reminderRecuringInterval != 'No' AND reminderActive))
                       GROUP BY reminder.reminderID", $this->person->get_id());
    $this->db->query($query);
    $reminders = array();
    while ($row = $this->db->row()) {
      $reminder = new reminder();
      $reminder->read_db_record($this->db);

      if ($reminder->is_alive()) {
        $reminderTime = format_date("U",$reminder->get_value("reminderTime"));

        // If repeating reminder
        if ($reminder->get_value('reminderRecuringInterval') != "No" && $reminder->get_value('reminderRecuringValue') != 0) {
          $interval = $reminder->get_value('reminderRecuringValue');
          $intervalUnit = $reminder->get_value('reminderRecuringInterval');

          while ($reminderTime < format_date("U",$this->last_date)+86400) {
            $row["reminderTime"] = $reminderTime;
            $reminders[date("Y-m-d",$reminderTime)][] = $row;
            $reminderTime = $reminder->get_next_reminder_time($reminderTime,$interval,$intervalUnit);
          }

        // Else if once off reminder
        } else {
          $row["reminderTime"] = $reminderTime;
          $reminders[date("Y-m-d",$reminderTime)][] = $row;
        }
      }
    }

    return $reminders;
  }

  function get_cal_tasks_to_start() {
    
    list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();
    // Select all tasks which are targetted to start
    $query = prepare("SELECT * 
                        FROM task 
                       WHERE personID = %d 
                         AND dateTargetStart >= '%s' 
                         AND dateTargetStart < '%s'
                         AND taskStatus NOT IN (".$ts_closed.")"
                    ,$this->person->get_id()
                    ,$this->first_date
                    ,$this->last_date);

    $this->db->query($query);
    $tasks_to_start = array();
    while ($row = $this->db->next_record()) {
      $tasks_to_start[$row["dateTargetStart"]][] = $row;
    }
    return $tasks_to_start;
  }

  function get_cal_tasks_to_complete() {

    list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();
    // Select all tasks which are targetted for completion
    $query = prepare("SELECT * 
                        FROM task 
                       WHERE personID = %d 
                         AND dateTargetCompletion >= '%s' 
                         AND dateTargetCompletion < '%s'
                         AND taskStatus NOT IN (".$ts_closed.")"
                    ,$this->person->get_id()
                    ,$this->first_date
                    ,$this->last_date);

    $this->db->query($query);
    $tasks_to_complete = array();
    while ($row = $this->db->next_record()) {
      $tasks_to_complete[$row["dateTargetCompletion"]][] = $row;
    }
    return $tasks_to_complete;
  }

  function get_cal_absences() {
    $query = prepare("SELECT * 
                        FROM absence
                       WHERE personID = %d
                         AND (dateFrom >= '%s' OR dateTo <= '%s')"
                    ,$this->person->get_id(),$this->first_date,$this->last_date);
    $this->db->query($query);
    $absences = array();
    while ($row = $this->db->row()) {
      $start_time = format_date("U",$row["dateFrom"]);
      $end_time = format_date("U",$row["dateTo"]);
      while ($start_time <= $end_time) {
        $absences[date("Y-m-d",$start_time)][] = $row;
        $start_time += 86400;
      }
    }

    return $absences;
  }

  function get_days_of_week_array($first_day) {
    // Generate a list of days, being mindful that a user may not want Sunday to be the first day of the week
    $days = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"); 
    foreach ($days as $day) {
      if (($day == $first_day || $go) && count($days_of_week) < 7) {
        $days_of_week[] = $day;
        $go = true;
      } 
    }
    return $days_of_week;
  }

  function set_return_mode($mode) {
    $this->rtp = $mode;
  }

  function draw() {
    global $TPL;

    $this->draw_canvas();
    $this->draw_row_header();

    $this->draw_body();

    $i = -7;
 

    while (date("D", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y"))) != $this->first_day_of_week) {
      $i++;
    }
    $i = $i - ($this->week_start * 7);
    $sunday_day = date("d", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y")));
    $sunday_month = date("m", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y")));
    $sunday_year = date("Y", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y")));

    $i = 0;

    $absences = $this->get_cal_absences();
    $reminders = $this->get_cal_reminders();
    $tasks_to_start = $this->get_cal_tasks_to_start();
    $tasks_to_complete = $this->get_cal_tasks_to_complete();

    // For each single week...
    while ($i < $this->weeks_to_display) {
      $this->draw_row();

      $a = 0;
      while ($a < 7) {
        $dates_of_week[$this->days_of_week[$a]] = date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i) + $a, $sunday_year));
        $a++;
      }


      foreach($dates_of_week as $day=>$date) {
 
        $d = new calendar_day();
        #$d->set_date(date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year));
        $d->set_date($date);
        $d->set_links($this->get_link_new_task($date).$this->get_link_new_reminder($date).$this->get_link_new_absence($date));


        // Tasks to be Started
        $tasks_to_start[$date] or $tasks_to_start[$date] = array();
        foreach($tasks_to_start[$date] as $t) {
          unset($extra);
          $t["timeLimit"] and $extra = " (".sprintf("Limit %0.1fhrs",$t["timeLimit"]).")";
          $d->start_tasks[] = '<a href="'.$TPL["url_alloc_task"].'taskID='.$t["taskID"].'">'.page::htmlentities($t["taskName"].$extra)."</a>";
        }

        // Tasks to be Completed
        $tasks_to_complete[$date] or $tasks_to_complete[$date] = array();
        foreach($tasks_to_complete[$date] as $t) {
          unset($extra);
          $t["timeLimit"] and $extra = " (".sprintf("Limit %0.1fhrs",$t["timeLimit"]).")";
          $d->complete_tasks[] = '<a href="'.$TPL["url_alloc_task"].'taskID='.$t["taskID"].'">'.page::htmlentities($t["taskName"].$extra)."</a>";
        }

        // Reminders
        $reminders[$date] or $reminders[$date] = array();
        foreach ($reminders[$date] as $r) {
          #if (date("Y-m-d",$r["reminderTime"]) == $date) {
            unset($wrap_start,$wrap_end);
            if (!$r["reminderActive"]) {
              $wrap_start = "<strike>";
              $wrap_end = "</strike>";
            }

            $text = page::htmlentities($r["reminderSubject"]);
            $r["reminderTime"] and $text = date("g:ia",$r["reminderTime"])." ".$text;
            $d->reminders[] = '<a href="'.$TPL["url_alloc_reminder"].'&step=3&reminderID='.$r["reminderID"].'&returnToParent='.$this->rtp.'&personID='.$r["personID"].'">'.$wrap_start.$text.$wrap_end.'</a>';
          #}
        }

        // Absences
        $absences[$date] or $absences[$date] = array();
        foreach ($absences[$date] as $a) {
          $d->absences[] = '<a href="'.$TPL["url_alloc_absence"].'absenceID='.$a["absenceID"].'&returnToParent='.$this->rtp.'">'.page::htmlentities($a["absenceType"]).'</a>';
        }

        $d->draw_day_html();

        $k++;
      }
      $i++;
      $this->draw_row_end();
    }
    $this->draw_body_end();
    $this->draw_canvas_end();
  }

  function draw_canvas() {
    echo "<table border='0' cellspacing='0' class='alloc_calendar' cellpadding='3'>";
  }
  function draw_canvas_end() {
    echo "</table>";
  }
  function draw_body() {
    # Unfortunately browser support for this seems to be quite bad. Eventually 
    # this should cause the table to have headers draw at the start of 
    # each page where the table is broken, but for now it doesn't seem to 
    # work.
    echo "<tbody>";
  }
  function draw_body_end() {
    echo "</tbody>";
  }
  function draw_row() {
    echo "\n<tr>";
  }
  function draw_row_end() {
    echo "</tr>";
  }
  function draw_row_header() {
    echo "\n<thead><tr>";
    foreach ($this->days_of_week as $day) {
      echo "<th>".$day."</th>";
    }
    echo "</tr></thead>";
  }

  function get_link_new_task($date) {
    global $TPL;
    $link = '<a href="'.$TPL["url_alloc_task"].'dateTargetStart='.$date.'&personID='.$this->person->get_id().'">';
    $link.= $this->get_img_new_task();
    $link.= "</a>";
    return $link;
  }

  function get_link_new_reminder($date) {
    global $TPL;
    $time = urlencode($date." 9:00am");
    $link = '<a href="'.$TPL["url_alloc_reminder"].'parentType=general&step=2&returnToParent='.$this->rtp.'&reminderTime='.$time;
    $link.= '&personID='.$this->person->get_id().'">';
    $link.= $this->get_img_new_reminder();
    $link.= "</a>";
    return $link;
  }

  function get_link_new_absence($date) {
    global $TPL;
    $link = '<a href="'.$TPL["url_alloc_absence"].'date='.$date.'&personID='.$this->person->get_id().'&returnToParent='.$this->rtp.'">';
    $link.= $this->get_img_new_absence();
    $link.= "</a>";
    return $link;
  }

  function get_img_new_task() {
    global $TPL;
    return "<img border=\"0\" src=\"".$TPL["url_alloc_images"]."task.gif\" alt=\"New Task\" title=\"New Task\">";
  }

  function get_img_new_reminder() {
    global $TPL;
    return "<img border=\"0\" src=\"".$TPL["url_alloc_images"]."reminder.gif\" alt=\"New Reminder\" title=\"New Reminder\">";
  }

  function get_img_new_absence() {
    global $TPL;
    return "<img border=\"0\" src=\"".$TPL["url_alloc_images"]."absence.gif\" alt=\"New Absence\" title=\"New Absence\">";
  }

}



?>
