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
  }

  function project_stats() {
    // date from which a project is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));

    $query = "SELECT * FROM project";
    $db = new db_alloc();
    $db_sub = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $project = new project();
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

      $query = prepare("SELECT * FROM projectPerson WHERE projectID=%d", $project->get_id());
      $db_sub->query($query);
      while ($db_sub->next_record()) {
        $projectPerson = new projectPerson();
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
    return $this->projects;
  }

  function task_stats() {
    $db = new db_alloc();

    list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();
    // Get total amount of current tasks for every person
    $q = "SELECT person.personID, person.username, count(taskID) as tally
            FROM task 
       LEFT JOIN person ON task.personID = person.personID 
           WHERE task.taskStatus NOT IN (".$ts_closed.")
        GROUP BY person.personID";

    $db->query($q);
    while ($db->next_record()) {
      $this->tasks["current"][$db->f("personID")] = $db->f("tally");
      $this->tasks["current"]["total"] += $db->f("tally");
    }
    
    // Get total amount of completed tasks for every person
    $q = "SELECT person.personID, person.username, count(taskID) as tally
            FROM task 
       LEFT JOIN person ON task.personID = person.personID 
           WHERE task.taskStatus NOT IN (".$ts_closed.")
        GROUP BY person.personID";

    $db->query($q);
    while ($db->next_record()) {
      $this->tasks["completed"][$db->f("personID")] = $db->f("tally");
      $this->tasks["completed"]["total"] += $db->f("tally");
    }


    // Get total amount of all tasks for every person
    $q = "SELECT person.personID, person.username, count(taskID) as tally
            FROM task 
       LEFT JOIN person ON task.personID = person.personID 
        GROUP BY person.personID";

    $db->query($q);
    while ($db->next_record()) {
      $this->tasks["total"][$db->f("personID")] = $db->f("tally");
      $this->tasks["total"]["total"] += $db->f("tally");
    }

    // date from which a task is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));
    // Get total amount of completed tasks for every person
    $q = prepare("SELECT person.personID, person.username, count(taskID) as tally, task.dateCreated
            FROM task 
       LEFT JOIN person ON task.personID = person.personID 
           WHERE ('%s' <= task.dateCreated)
        GROUP BY person.personID",$date);

    $db->query($q);
    while ($db->next_record()) {
      $d = format_date("Y-m-d", $db->f("dateCreated"));
      $this->tasks["new"][$db->f("personID")][$d] = $db->f("tally");
      $v += $db->f("tally");  
      $this->tasks["new"]["total"][$d] = $v;
    }
   
                

    return $this->tasks;
  }

  function comment_stats() {
    // date from which a comment is counted as being new. if monday then date back to friday, else the previous day
    $days = date("w") == 1 ? 3 : 1;
    $date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $days, date("Y")));

    $query = "SELECT * FROM comment";
    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment();
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
    return $this->comments;
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
    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $person = new person();
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
      $person = new person();
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
