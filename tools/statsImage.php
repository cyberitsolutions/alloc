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


require_once("../alloc.php");

$stats = new stats();
$projects = $stats->project_stats();
$tasks = $stats->task_stats();
$comments = $stats->comment_stats();


$id = $_GET["id"];
$width = $_GET["width"];
$multiplier = $_GET["multiplier"];
$labels = $_GET["labels"];


$db = new db_alloc();
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

header("Content-type: image/png");
imagepng($image);



?>
