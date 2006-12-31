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

class task_calendar {
  var $date;

  function show_task_calendar_recursive($template) {
    global $current_user, $TPL;

    $personID = $_GET["personID"] or $personID = $_POST["personID"];
    if ($personID) {
      $person = new person;
      $person->set_id($personID);
      $person->select();
    } else {
      $person = $current_user; 
    }

 
    $tasksGraphPlotHome = $current_user->prefs["tasksGraphPlotHome"];
    $tasksGraphPlotHomeStart = $current_user->prefs["tasksGraphPlotHomeStart"];
  

    if (!$tasksGraphPlotHome && !isset($tasksGraphPlotHome)) {
      $tasksGraphPlotHome = 2;
    }

    if (!$tasksGraphPlotHomeStart || $tasksGraphPlotHomeStart == "Zero") {
      $tasksGraphPlotHomeStart = "0";
    }
    $i = -7;

    while (date("D", mktime(0, 0, 0, date("m"), date("d") + $i, date("Y"))) != "Sun") {
      $i++;
    }
    $i = $i - ($tasksGraphPlotHomeStart * 7);
    $sunday_day = date("d", mktime(0, 0, 0, date(m), date(d) + $i, date(Y)));
    $sunday_month = date("m", mktime(0, 0, 0, date(m), date(d) + $i, date(Y)));
    $sunday_year = date("Y", mktime(0, 0, 0, date(m), date(d) + $i, date(Y)));

    $days_of_week = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");

    $db = new db_alloc;
    $i = 0;


    /// Need to fetch the first and latest date on the page so that we only genereate repeating reminders up to the maximum date displayed (as opposed to inifinity)
    $first_date_on_page = mktime(date("H")
                                ,date("i")
                                ,date("s")
                                ,date("m")
                                ,date("d")-($tasksGraphPlotHomeStart*7)-7
                                ,date("Y"));

    $last_date_on_page = mktime(date("H",$first_date_on_page)
                               ,date("i",$first_date_on_page)
                               ,date("s",$first_date_on_page)
                               ,date("m",$first_date_on_page)
                               ,date("d",$first_date_on_page)+(($tasksGraphPlotHome*7)-1)
                               ,date("Y",$first_date_on_page));


    // Pre-load all the users reminders 
    $query = sprintf("SELECT * 
                        FROM reminder 
                       WHERE personID = %d", $person->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $reminder = new reminder;
      $reminder->read_db_record($db);

      if ($reminder->is_alive()) {
        $reminderTime = format_date("U",$reminder->get_value("reminderTime"));

        // If repeating reminder
        if ($reminder->get_value('reminderRecuringInterval') != "No" && $reminder->get_value('reminderRecuringValue') != 0) {
          $interval = $reminder->get_value('reminderRecuringValue');
          $intervalUnit = $reminder->get_value('reminderRecuringInterval');
          
          while ($reminderTime <= $last_date_on_page) {
            $reminders[] = array("reminderID"=>$reminder->get_id(),"reminderSubject"=>$reminder->get_value("reminderSubject"),"reminderTime"=>$reminderTime);
            $reminderTime = $reminder->get_next_reminder_time($reminderTime,$interval,$intervalUnit);
          }

        // Else if once off reminder
        } else {
          $reminders[] = array("reminderID"=>$reminder->get_id(),"reminderSubject"=>$reminder->get_value("reminderSubject"),"reminderTime"=>$reminderTime);
        }
      }
    }


    while ($i < $tasksGraphPlotHome) {

      foreach($days_of_week as $day) {
        unset($TPL["calendar_".$day."_started"]);
        unset($TPL["calendar_".$day."_completed"]);
        unset($TPL["calendar_".$day."_reminders"]);
        unset($TPL["to_be_started_".$day]);
        unset($TPL["to_be_completed_".$day]);
        unset($TPL["reminders_".$day]);
      }


      $a = 0;
      while ($a < 7) {
        $dates_of_week[$days_of_week[$a]] = date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i) + $a, $sunday_year));
        $a++;
      }


      $date1 = date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i), $sunday_year));
      $date2 = date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * ($i + 1)), $sunday_year));

      // select all tasks which are targetted for this week
      $query = sprintf("SELECT * 
                          FROM task 
                         WHERE personID = %d 
                           AND ((dateTargetCompletion >= '%s' AND dateTargetCompletion < '%s') OR (dateTargetStart >= '%s' AND dateTargetStart < '%s'))
                           AND dateActualCompletion IS NULL", $person->get_id(), $date1, $date2, $date1, $date2);
      $db->query($query);
      while ($db->next_record()) {
        foreach($dates_of_week as $day=>$date) {

          if ($db->f("dateTargetStart") == $date) {
            ${"to_be_started_".$day} = "<br><br/>To be started:";
            unset($extra);
            $db->f("timeEstimate") and $extra = " (".sprintf("Est %0.1fhrs",$db->f("timeEstimate")).")";
            $TPL["calendar_".$day."_started"].= '<br/><a href="'.$TPL["url_alloc_task"].'taskID='.$db->f("taskID").'">'.$db->f("taskName").$extra."</a>";
          }

          if ($db->f("dateTargetCompletion") == $date) {
            $TPL["calendar_".$day."_started"] or $br = "<br/>";
            ${"to_be_completed_".$day} = $br."<br/>To be completed:";
            unset($extra);
            $db->f("timeEstimate") and $extra = " (".sprintf("Est %0.1fhrs",$db->f("timeEstimate")).")";
            $TPL["calendar_".$day."_completed"].= '<br/><a href="'.$TPL["url_alloc_task"].'taskID='.$db->f("taskID").'">'.$db->f("taskName").$extra."</a>";
          }

        }
      }

      // Paint reminders on 
      foreach($dates_of_week as $day=>$date) {
        $reminders or $reminders = array();
        foreach ($reminders as $reminder => $r) {
          if (date("Y-m-d",$r["reminderTime"]) == $date) {
            ${"reminders_".$day} = "<br/><br/>Reminders:";
            $TPL["calendar_".$day."_reminders"].= '<br/><a href="'.$TPL["url_alloc_reminderAdd"].'&step=3&reminderID='.$r["reminderID"].'">'.$r["reminderSubject"]."</a>";
          }
        }
      }


      // Draw day numbers and Today square.
      foreach($days_of_week as $k=>$day) {

        $calendar_date = date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year));

        $TPL["calendar_".$day."_date"] = date("j-M", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year));


        // Toggle every second month to have slightly different coloured shading
        $month_num = date("n", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year));

        $TPL["calendar_class_".$day] = "";
        if ($calendar_date == date("Y-m-d")) {
          $TPL["calendar_class_".$day] = " class=\"today\"";

        } else if ($month_num % 2 == 0) {
          $TPL["calendar_class_".$day] = " class=\"even\"";
        }

        $reminderTime = urlencode($calendar_date." 9:00am");
        $TPL["calendar_".$day."_links"] = "<a href=\"".$TPL["url_alloc_task"]."dateTargetStart=".$calendar_date."\"><img border=\"0\" src=\"".$TPL["url_alloc_images"]."task.png\" alt=\"New Task\"></a>";
        $TPL["calendar_".$day."_links"].= "<a href=\"".$TPL["url_alloc_reminderAdd"]."parentType=general&step=2&returnToParent=t&reminderTime=".$reminderTime."\"><img border=\"0\" src=\"".$TPL["url_alloc_images"]."reminder.png\" alt=\"New Reminder\"></a>";

        if ($TPL["calendar_".$day."_started"]) {
          $TPL["calendar_".$day."_started"] = ${"to_be_started_".$day}.$TPL["calendar_".$day."_started"];
        }
        if ($TPL["calendar_".$day."_completed"]) {
          $TPL["calendar_".$day."_completed"] = ${"to_be_completed_".$day}.$TPL["calendar_".$day."_completed"];
        }
        if ($TPL["calendar_".$day."_reminders"]) {
          $TPL["calendar_".$day."_reminders"] = ${"reminders_".$day}.$TPL["calendar_".$day."_reminders"];
        }

      }

      include_template($template);
      $i++;
    }
  }

}



?>
