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

class task_graph_home_item extends home_item {
  var $date;

  function task_graph_home_item() {
    home_item::home_item("task_graph_home_item", "Task Calendar", "project", "taskGraphH.tpl");
  }



  function show_task_calendar() {
    global $TPL, $plot_weeks, $tasksGraphPlotHome, $plot_weeks_back, $tasksGraphPlotHomeStart, $current_user;

    if (!is_object($current_user)) {
      return false;
    }

    if (isset($plot_weeks)) {
      $tasksGraphPlotHome = $plot_weeks;
    }

    if (isset($plot_weeks_back)) {
      $tasksGraphPlotHomeStart = $plot_weeks_back;
    }

    is_object($current_user) and $current_user->prefs["tasksGraphPlotHome"] = $tasksGraphPlotHome;
    is_object($current_user) and $current_user->prefs["tasksGraphPlotHomeStart"] = $tasksGraphPlotHomeStart;


    $week_links = array("0", 1, 2, 3, 4, 8, 12, 30, 52);

    foreach($week_links as $week) {
      $TPL["forward_week_links"].= "&nbsp;";
      $TPL["back_week_links"].= "&nbsp;";
      if ($week == $tasksGraphPlotHome) {
        $TPL["forward_week_links"].= $week;
      } else {
        $TPL["forward_week_links"].= "<a href=\"".$TPL["url_alloc_home"]."plot_weeks=".$week."\">".$week."</a>";
      }
      if ($week == $tasksGraphPlotHomeStart) {
        $TPL["back_week_links"].= $week;
      } else {
        $TPL["back_week_links"].= "<a href=\"".$TPL["url_alloc_home"]."plot_weeks_back=".$week."\">".$week."</a>";
      }
    }
  }



  function show_task_calendar_recursive($template) {
    global $tasksGraphPlotHome, $current_user, $TPL, $tasksGraphPlotHomeStart;

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
                           AND dateActualCompletion IS NULL", $current_user->get_id(), $date1, $date2, $date1, $date2);
      $db->query($query);
      while ($db->next_record()) {
        foreach($dates_of_week as $day=>$date) {

          if ($db->f("dateTargetStart") == $date) {
            ${"to_be_started_".$day} = "<br><br>To be started:";
            $TPL["calendar_".$day."_started"].= '<tr><td valign=\"top\">*</td><td width="99%"><a href="'.$TPL["url_alloc_task"].'&taskID='.$db->f("taskID").'">';
            $TPL["calendar_".$day."_started"].= $db->f("taskName")."</a></td></tr>";
          }

          if ($db->f("dateTargetCompletion") == $date) {
            $TPL["calendar_".$day."_started"] or $br = "<br>";
            ${"to_be_completed_".$day} = $br."<br>To be completed:";
            $TPL["calendar_".$day."_completed"].= '<tr><td valign=\"top\">*</td><td width="99%"><a href="'.$TPL["url_alloc_task"].'&taskID='.$db->f("taskID").'">';
            $TPL["calendar_".$day."_completed"].= $db->f("taskName")."</a></td></tr>";
          }

        }
      }

      // select all reminders which are targetted for this week
      $query = sprintf("SELECT * 
                          FROM reminder 
                         WHERE personID = %d AND reminderTime >= '%s' AND reminderTime < '%s'", $current_user->get_id(), $date1, $date2);

      $reminder = new reminder;
      $db->query($query);
      while ($db->next_record()) {
        foreach($dates_of_week as $day=>$date) {
          $reminder->read_db_record($db);
	        if ($reminder->is_alive()) {
            if (date("Y-m-d", strtotime($db->f("reminderTime"))) == $date) {
              ${"reminders_".$day} = "<br><br>Reminders:";
              $TPL["calendar_".$day."_reminders"].= '<tr><td valign=\"top\">*</td><td width="99%"><a href="'.$TPL["url_alloc_reminderAdd"].'&step=3&reminderID='.$db->f("reminderID").'">';
              $TPL["calendar_".$day."_reminders"].= $db->f("reminderSubject")."</a></td></tr>";
            }
          }
        }
      }

      $table = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">";

      // Draw day numbers and Today square.
      foreach($days_of_week as $k=>$day) {
        $TPL["calendar_".$day."_date"] = date("jS M", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year));
        $TPL["calendar_class_".$day] = "";
        if (date("Y-m-d", mktime(0, 0, 0, $sunday_month, $sunday_day + (7 * $i + $k), $sunday_year)) == date("Y-m-d")) {
          $TPL["calendar_class_".$day] = " class=\"today\"";
        }

        if ($TPL["calendar_".$day."_started"]) {
          $TPL["calendar_".$day."_started"] = ${"to_be_started_".$day}
          .$table.$TPL["calendar_".$day."_started"]."</table>";
        }
        if ($TPL["calendar_".$day."_completed"]) {
          $TPL["calendar_".$day."_completed"] = ${"to_be_completed_".$day}
          .$table.$TPL["calendar_".$day."_completed"]."</table>";
        }
        if ($TPL["calendar_".$day."_reminders"]) {
          $TPL["calendar_".$day."_reminders"] = ${"reminders_".$day}
          .$table.$TPL["calendar_".$day."_reminders"]."</table>";
        }

      }

      include_template($template);
      $i++;
    }
  }




}



?>
