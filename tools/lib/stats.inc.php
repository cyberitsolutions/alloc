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

class stats {
  var $classname = "stats";

  var $projects = array("all"=>array("total"=>array( /* <date>=> <number> */ )
                                       // <uid>=> array( <date>=> <number>, .. ),
                        ), "new"=>array("total"=>array( /* <date>=> <number> */ )
                                          // <uid>=> array( <date>=> <number>, .. ),
                        ), "current"=>array("total"=>0 /* <uid>=> <number>, .. */ ),
                        "archived"=>array("total"=>0 /* <uid>=> <number>, .. */ ),
                        "total"=>array("total"=>0)
    );
  var $tasks = array("all"=>array("total"=>array( /* <date>=> <number> */ )
                                    // <uid>=> array( <date>=> <number>, .. ),
                     ), "new"=>array("total"=>array( /* <date>=> <number> */ )
                                       // <uid>=> array( <date>=> <number>, .. ),
                     ), "current"=>array("total"=>0), "completed"=>array("total"=>0), "total"=>array("total"=>0)
    );
  var $comments = array("all"=>array("total"=>array( /* <date>=> <number> */ )
                                       // <uid>=> array( <date>=> <number>, .. ),
                        ), "new"=>array("total"=>array( /* <date>=> <number> */ )
                                          // <uid>=> array( <date>=> <number>, .. ),
                        ), "total"=>array("total"=>0)
    );
  var $persons = array();

  function stats() {
    $this->project_stats();
    $this->task_stats();
    $this->comment_stats();
    $this->order_by_most_frequent_use();
  }

  function project_stats() {
    // date from which a project is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));

    $query = "SELECT * FROM project";
    $db = new db_alloc;
    $db_sub = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $project = new project;
      $project->read_db_record($db);
      $this->projects["total"]["total"]++;

      switch ($project->get_value("projectStatus")) {
      case ("current"):
      case ("overdue"):
        $this->projects["current"]["total"]++;
        break;
      case ("archived"):
        $this->projects["archived"]["total"]++;
        break;
      }

      $query = sprintf("SELECT * FROM projectPerson WHERE projectID='%d'", $project->get_id());
      $db_sub->query($query);
      while ($db_sub->next_record()) {
        $projectPerson = new projectPerson;
        $projectPerson->read_db_record($db_sub);
        $this->projects["total"][$projectPerson->get_value("personID")]++;
        switch ($project->get_value("projectStatus")) {
          case ("current"):
          case ("overdue"):
            $this->projects["current"][$projectPerson->get_value("personID")]++;
          break;
          case ("archived"):
            $this->projects["archived"][$projectPerson->get_value("personID")]++;
          break;
        }
        if ($project->get_value("dateActualStart") != "") {
          if (!isset($this->projects["all"][$projectPerson->get_value("personID")])) {
            $this->projects["all"][$projectPerson->get_value("personID")] = array();
          }
          $this->projects["all"][$projectPerson->get_value("personID")][$project->get_value("dateActualStart")]++;
          $this->projects["all"][$projectPerson->get_value("personID")]["total"]++;
          $this->projects["all"]["total"][$project->get_value("dateActualStart")]++;

          if (strcmp($date, $project->get_value("dateActualStart")) <= 0) {
            if (!isset($this->projects["new"][$projectPerson->get_value("personID")])) {
              $this->projects["new"][$projectPerson->get_value("personID")] = array();
            }
            $this->projects["new"][$projectPerson->get_value("personID")][$project->get_value("dateActualStart")]++;
            $this->projects["new"][$projectPerson->get_value("personID")]["total"]++;
            $this->projects["new"]["total"][$project->get_value("dateActualStart")]++;
          }
        }
      }
    }
  }

  function task_stats() {
    // date from which a task is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));

    $query = "SELECT * FROM task";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $task = new task;
      $task->read_db_record($db);

      if (!$task->get_value("dateActualCompletion")) {
        $this->tasks["current"][$task->get_value("personID")]++;
        $this->tasks["current"]["total"]++;
      } else {
        $this->tasks["completed"][$task->get_value("personID")]++;
        $this->tasks["completed"]["total"]++;
      }

      if ($task->get_value("dateActualStart") != "") {
        if (!isset($this->tasks["all"][$task->get_value("personID")])) {
          $this->tasks["all"][$task->get_value("personID")] = array();
        }
        $this->tasks["all"][$task->get_value("personID")][$task->get_value("dateActualStart")]++;
        $this->tasks["all"][$task->get_value("personID")]["total"]++;
        $this->tasks["all"]["total"][$task->get_value("dateActualStart")]++;

        if (strcmp($date, $task->get_value("dateActualStart")) <= 0) {
          if (!isset($this->tasks["new"][$task->get_value("personID")])) {
            $this->tasks["new"][$task->get_value("personID")] = array();
          }
          $this->tasks["new"][$task->get_value("personID")][$task->get_value("dateActualStart")]++;
          $this->tasks["new"][$task->get_value("personID")]["total"]++;
          $this->tasks["new"]["total"][$task->get_value("dateActualStart")]++;
        }
      }
      $this->tasks["total"][$task->get_value("personID")]++;
      $this->tasks["total"]["total"]++;
    }
  }

  function comment_stats() {
    // date from which a comment is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));

    $query = "SELECT * FROM comment";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment;
      $comment->read_db_record($db);
      $this->comments["total"][$comment->get_value("commentModifiedUser")]++;
      $this->comments["total"]["total"]++;
      if ($comment->get_value("commentModifiedTime") != "") {
        if (!isset($this->comments["all"][$comment->get_value("commentModifiedUser")])) {
          $this->comments["all"][$comment->get_value("commentModifiedUser")] = array();
        }
        $this->comments["all"][$comment->get_value("commentModifiedUser")][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;
        $this->comments["all"][$comment->get_value("commentModifiedUser")]["total"]++;
        $this->comments["all"]["total"][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;

        if (strcmp($date, $comment->get_value("commentModifiedTime")) <= 0) {
          if (!isset($this->comments["new"][$comment->get_value("commentModifiedUser")])) {
            $this->comments["new"][$comment->get_value("commentModifiedUser")] = array();
          }
          $this->comments["new"][$comment->get_value("commentModifiedUser")][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;
          $this->comments["new"][$comment->get_value("commentModifiedUser")]["total"]++;
          $this->comments["new"]["total"][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;
        }
      }
    }
  }

  function compare($a, $b) {
    if ($a["count_back"] == $b["count_back"]) {
      // if last added item was added on the same day then look at how many were added on that day
      if ($a["value"] == $b["value"]) {
        // if the same number of items added on same day then sort alphabetically
        return strcmp($a["username"], $b["username"]);
      } else {
        return ($a["value"] < $b["value"]) ? 1 : -1;
      }
    } else {
      return ($a["count_back"] < $b["count_back"]) ? -1 : 1;
    }
  }

  function order_by_most_frequent_use() {
    $max_search_back = 90;      // maximum number of days to go back when sorting

    $query = "SELECT * FROM person ORDER BY username";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $person = new person;
      $person->read_db_record($db);

      for ($value = 0, $i = 0; $value == 0 && $i < $max_search_back; $i++) {
        $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $i, date("Y")));
        $value = $this->tasks["all"][$person->get_id()][$date];
        // + $this->projects["all"][$person->get_id()][$date]
        // + $this->comments["all"][$person->get_id()][$date];
        if ($value > 0) {
          array_push($this->persons, array("id"=>$person->get_id(), "username"=>$person->get_value('username'), "count_back"=>$i, "value"=>$value));
        }
      }
    }

    usort($this->persons, array($this, "compare"));
  }

  function get_stats_for_email($format) {

    if ($format == "html") {
      $msg = "<br><br><h4>Alloc Stats For Today</h4>";
      $msg.= sprintf("%d New and %d Active Projects<br><br>", $this->projects["new"]["total"], $this->projects["current"]["total"]);
      $msg.= "<table >";
    } else {
      $msg = "\n- - - - - - - - - -\n";
      $msg.= "Alloc Stats For Today\n";
      $msg.= "\n";
      $msg.= sprintf("%d New and %d Active Projects\n", $this->projects["new"]["total"], $this->projects["current"]["total"]);
      $msg.= "\n";
      $msg.= "Top Users:\n";
    }

    $num_users = 3;
    for ($i = 0; $i < $num_users && $i < count($this->persons); $i++) {
      $person = new person;
      $person->set_id($this->persons[$i]["id"]);
      $person->select();

      if ($format == "html") {
        $msg.= "<tr>";
        $msg.= sprintf("<td>%s has</td>", $person->get_value("username"));
        $msg.= sprintf("<td>%d New and</td>", $this->tasks["new"][$person->get_id()]["total"]);
        $msg.= sprintf("<td>%d Active Tasks</td>", $this->tasks["current"][$person->get_id()]);
        $msg.= "</tr>";
      } else {
        $msg.= sprintf("* %-15s %-15s %s\n", sprintf("%s has", $person->get_value("username")), sprintf("%d New and", $this->tasks["new"][$person->get_id()]["total"]), sprintf("%d Active Tasks", $this->tasks["current"][$person->get_id()]));
      }
    }

    if ($format == "html") {
      $msg.= "<hr />\nTo disable these daily emails, change the \"Daily Email\" setting on the Personal page.\n";
      $msg.= "</table>";
    } else {
      $msg.= "\n- - - - - - - - - -\nTo disable these daily emails, change the \"Daily Email\" setting on the Personal page.\n";
    }

    return $msg;
  }

}



?>
