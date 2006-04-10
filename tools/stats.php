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

#if ($_GET["web"]) {
  #define("NO_AUTH",false);
#} else {
  #define("NO_AUTH",true);
#}
require_once("alloc.inc");

$db = new db_alloc;
$db_sub = new db_alloc;

$projects = array("new"=>array("total"=>array( /* <date>=> <number> */ )
                                 // <uid>=> array( <date>=> <number>, .. ),
                  ), "current"=>array("total"=>0 /* <uid>=> <number>, .. */ ),
                  "archived"=>array("total"=>0 /* <uid>=> <number>, .. */ )
  );

$query = "SELECT * FROM project";
$db->query($query);
while ($db->next_record()) {
  $project = new project;
  $project->read_db_record($db);

  $query = sprintf("SELECT * FROM projectPerson WHERE projectID='%d'", $project->get_id());
  $db_sub->query($query);
  while ($db_sub->next_record()) {
    $projectPerson = new projectPerson;
    $projectPerson->read_db_record($db_sub);
    switch ($project->get_value("projectStatus")) {
    case ("current"):
    case ("overdue"):
      $projects["current"][$projectPerson->get_value("personID")]++;
      $projects["current"]["total"]++;
      break;
    case ("archived"):
      $projects["archived"][$projectPerson->get_value("personID")]++;
      $projects["archived"]["total"]++;
      break;
    }
    if ($project->get_value("dateActualStart") != "") {
      if (!isset($projects["new"][$projectPerson->get_value("personID")])) {
        $projects["new"][$projectPerson->get_value("personID")] = array();
      }
      $projects["new"][$projectPerson->get_value("personID")][$project->get_value("dateActualStart")]++;
      $projects["new"]["total"][$project->get_value("dateActualStart")]++;
    }
  }
}

$tasks = array("new"=>array("total"=>array( /* <date>=> <number> */ )
                              // <uid>=> array( <date>=> <number>, .. ),
               ), "current"=>array("total"=>0), "completed"=>array("total"=>0)
  );

$query = "SELECT * FROM task";
$db->query($query);
while ($db->next_record()) {
  $task = new task;
  $task->read_db_record($db);
  switch ($task->get_value("dateActualCompletion")) {
  case (""):
    $tasks["current"][$task->get_value("personID")]++;
    $tasks["current"]["total"]++;
    break;
  default:
    $tasks["completed"][$task->get_value("personID")]++;
    $tasks["completed"]["total"]++;
    break;
  }
  if ($task->get_value("dateActualStart") != "") {
    if (!isset($tasks["new"][$task->get_value("personID")])) {
      $tasks["new"][$task->get_value("personID")] = array();
    }
    $tasks["new"][$task->get_value("personID")][$task->get_value("dateActualStart")]++;
    $tasks["new"]["total"][$task->get_value("dateActualStart")]++;
  }
}

$comments = array("new"=>array("total"=>array( /* <date>=> <number> */ )
                                 // <uid>=> array( <date>=> <number>, .. ),
                  ), "total"=>array("total"=>0)
  );

$query = "SELECT * FROM comment";
$db->query($query);
while ($db->next_record()) {
  $comment = new comment;
  $comment->read_db_record($db);
  $comments["total"][$comment->get_value("commentModifiedUser")]++;
  $comments["total"]["total"]++;
  if ($comment->get_value("commentModifiedTime") != "") {
    if (!isset($comments["new"][$comment->get_value("commentModifiedUser")])) {
      $comments["new"][$comment->get_value("commentModifiedUser")] = array();
    }
    $comments["new"][$comment->get_value("commentModifiedUser")][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;
    $comments["new"]["total"][date("Y-m-d", strtotime($comment->get_value("commentModifiedTime")))]++;
  }
}

/* 
   $clients = array( "total"=> 0, "current"=> 0, "potential"=> 0, "archived"=> 0 );

   $query = "SELECT * FROM client"; $db->query($query); while($db->next_record()) { $client = new client; $client->read_db_record($db); switch($client->get_value("clientStatus")) { case "current" : $clients["current"]++; break; case "potential" : $clients["potential"]++; break; case "archived" :
   $clients["archived"]++; break; } $clients["total"]++; } */


$TPL["global_projects_current"] = $projects["current"]["total"];
$TPL["global_projects_total"] = $projects["current"]["total"]
  + $projects["archived"]["total"];

$TPL["global_tasks_current"] = $tasks["current"]["total"];
$TPL["global_tasks_total"] = $tasks["current"]["total"]
  + $tasks["completed"]["total"];

$TPL["global_comments_total"] = $comments["total"]["total"];

/* 
   $TPL["global_clients_current"] = $clients["current"]; $TPL["global_clients_potential"] = $clients["potential"]; $TPL["global_clients_archived"] = $clients["archived"]; $TPL["global_clients_total"] = $clients["current"] + $clients["potential"] + $clients["archived"]; */
$TPL["global_graph"] = draw_graph("total", 400, 2, "", false);
$TPL["global_graph_big"] = draw_graph("total", 640, 8, "big_", true);

include_template("templates/statsM.tpl");


function draw_graph($id, $width, $multiplier, $prefix, $labels) {
  global $TPL, $projects, $tasks, $comments, $db;

  $start_date = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));

  $height = 0;
  $top_margin = 0;
  $bottom_margin = 0;
  $left_margin = 0;
  $right_margin = 0;

  for ($date = $start_date; $date < time(); $date += 86400) {
    $count = ($projects["new"][$id][date("Y-m-d", $date)]
              + $tasks["new"][$id][date("Y-m-d", $date)]
              + $comments["new"][$id][date("Y-m-d", $date)]);
    if ($height < $count) {
      $height = $count;
    }
  }
  if ($labels) {
    if ($height > 0) {
      $left_margin = strlen($height) * 5 + 10;
    } else {
      $left_margin = 5;
    }
    $right_margin = 5;
    $top_margin = 5;
    $bottom_margin = 10;
  }
  $disp_height = $height;
  $height *= $multiplier;
  $height += ($top_margin + $bottom_margin);
  if ($height < 18) {
    $height = 18;
  }

  $segment_size = ($width - $left_margin - $right_margin) / ((($date - $start_date) / 86400) - 1);

  /* height = max of sum of projects+tasks+comments for each day + margins */
  $image = imageCreate($width, $height);

  $color_background = imageColorAllocate($image, 255, 255, 255);
  $color_projects = imageColorAllocate($image, 0, 0, 255);
  $color_tasks = imageColorAllocate($image, 0, 128, 0);
  $color_comments = imageColorAllocate($image, 255, 0, 0);
  $color_foreground = imageColorAllocate($image, 0, 0, 0);

  /* fill background */
  imageFilledRectangle($image, 0, 0, $width - 1, $height - 1, $colors_background);
  imagecolortransparent($image, $color_background);

  $projects_points = array(0=>$left_margin, 1=>$height - $bottom_margin - 1);
  $tasks_points = array(0=>$left_margin, 1=>$height - $bottom_margin - 1);
  $comments_points = array(0=>$left_margin, 1=>$height - $bottom_margin - 1);
  $count = 2;
  $x_pos = $left_margin;
  for ($date = $start_date; $date < time(); $date += 86400) {
    $projects_points[$count] = $x_pos;
    $tasks_points[$count] = $x_pos;
    $comments_points[$count] = $x_pos;
    $x_pos += $segment_size;
    $count++;

    $y_pos = $height - $bottom_margin - 1 - ($projects["new"][$id][date("Y-m-d", $date)] * $multiplier);
    $projects_points[$count] = $y_pos;
    $y_pos -= ($tasks["new"][$id][date("Y-m-d", $date)] * $multiplier);
    $tasks_points[$count] = $y_pos;
    $y_pos -= ($comments["new"][$id][date("Y-m-d", $date)] * $multiplier);
    $comments_points[$count] = $y_pos;
    $count++;
  }
  $projects_points[$count] = $width - $right_margin - 1;
  $projects_points[$count + 1] = $height - $bottom_margin - 1;
  $tasks_points[$count] = $width - $right_margin - 1;
  $tasks_points[$count + 1] = $height - $bottom_margin - 1;
  $comments_points[$count] = $width - $right_margin - 1;
  $comments_points[$count + 1] = $height - $bottom_margin - 1;

  imagefilledpolygon($image, $comments_points, ($count + 2) / 2, $color_comments);
  imagefilledpolygon($image, $tasks_points, ($count + 2) / 2, $color_tasks);
  imagefilledpolygon($image, $projects_points, ($count + 2) / 2, $color_projects);

  if ($labels) {
    /* baseline */
    imageline($image, $left_margin, $height - $bottom_margin - 1, $width - $right_margin - 1, $height - $bottom_margin - 1, $color_foreground);
    /* dates */
    imagestring($image, 2, $left_margin, $height - $bottom_margin - 1, date("Y-m-d", $start_date), $color_foreground);
    imagestring($image, 2, $width - $right_margin - 60, $height - $bottom_margin - 1, date("Y-m-d"), $color_foreground);

    /* sideline */
    imageline($image, $left_margin, $height - $bottom_margin - 1, $left_margin, $top_margin - 1, $color_foreground);
    /* max count */
    if ($disp_height > 0) {
      imagestring($image, 2, 5, 0, $disp_height, $color_foreground);
    }
  }

  $image_title = sprintf("../images/%suser%d.png", $prefix, $id);

  imagepng($image, $image_title);
  return $image_title;
}

  /* order array of userids from most actions to least actions */
function compare($a, $b) {
  global $projects, $tasks, $comments;

  for ($i = 0, $a_value = 0, $b_value = 0; $a_value == $b_value && $i < 30; $i++) {
    $date = mktime(0, 0, 0, date("m"), date("d") - $i, date("Y"));
    $a_value = $comments["new"][$a][date("Y-m-d", $date)] + $projects["new"][$a][date("Y-m-d", $date)]
      + $tasks["new"][$a][date("Y-m-d", $date)];
    $b_value = $comments["new"][$b][date("Y-m-d", $date)] + $projects["new"][$b][date("Y-m-d", $date)]
      + $tasks["new"][$b][date("Y-m-d", $date)];
  }
  if ($a_value == $b_value)
    return 0;
  return ($a_value < $b_value) ? 1 : -1;
}

function show_users_stats($template) {
  global $TPL, $projects, $tasks, $comments, $db;

  $persons = array();

  $query = "SELECT * FROM person ORDER BY username";
  $db->query($query);
  while ($db->next_record()) {
    $person = new person;
    $person->read_db_record($db);
    array_push($persons, $person->get_id());
  }

  usort($persons, "compare");

  for ($i = 0; $i < count($persons); $i++) {
    $person = new person;
    $person->set_id($persons[$i]);
    $person->select();

    $TPL["user_username"] = $person->get_value("username");

    $TPL["user_projects_current"] = $projects["current"][$person->get_id()] + 0;
    /* +0 makes sure that it is always a number and not just NULL */
    $TPL["user_projects_total"] = $projects["current"][$person->get_id()]
      + $projects["archived"][$person->get_id()];

    $TPL["user_tasks_current"] = $tasks["current"][$person->get_id()] + 0;
    $TPL["user_tasks_total"] = $tasks["current"][$person->get_id()]
      + $tasks["completed"][$person->get_id()];

    $TPL["user_comments_total"] = $comments["total"][$person->get_id()] + 0;

    $TPL["user_graph"] = draw_graph($person->get_id(), 400, 2, "", false);
    $TPL["user_graph_big"] = draw_graph($person->get_id(), 640, 8, "big_", true);

    if ($TPL["user_projects_total"] + $TPL["user_tasks_total"]
        + $TPL["user_comments_total"] > 0) {
      $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
      include_template($template);
    }
  }
}











?>
