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

define("TICK_SIZE", 7); // Days between tick marks along the axis
define("LARGE_TICK_SIZE", 60);  // Days between tick marks along the axis
define("MAX_TICKS", 20);        // Days between tick marks along the axis

  function echo_debug($s) {
  #echo $s;
  }

        // Outputs an image with an error message given by $s and then terminates the script
  function image_die($s) {

    $image = imageCreate(950, 40);

    // allocate all required colors
    $color_background = imageColorAllocate($image, 255, 255, 255);
    $color_text = imageColorAllocate($image, 0, 0, 64);

    // clear the image space with the background color
    imageFilledRectangle($image, 0, 0, 200 - 1, 50 - 1, $this->color_background);

    imageString($image, 5, 3, 10, $s, $color_text);


    if (ALLOC_GD_IMAGE_TYPE == "PNG") {
      header("Content-type: image/png");
      imagePNG($image);
    } else {
      header("Content-type: image/gif");
      imageGIF($image);
    }
    exit();

  }

class task_graph
{
  // 'public' variables

  // size parameters (all in pixels)
  var $width = 950;             // Width of the image
  var $top_margin = 20;         // Distance of first bar from top of image
  var $left_margin = 400;
  var $right_margin = 10;
  var $bottom_margin = 100;
  var $task_padding = 4;        // Whitespace above and below each task bar
  var $bar_height = 8;          // Height of each task bar
  var $indent_increment = 20;   // Increase in whitespace to left of task for child tasks

  // 'private' variables
  var $y;                       // current y position
  var $image;                   // image handle
  var $task_colors;             // color handles for task bars
  var $height;                  // image height - set dynamically according to number of tasks and other variables such as bar_height
  var $color_background;        // Background colour of image
  var $color_text;              // Colour of text on image
  var $color_grid;              // Colour of grid lines behind bars
  var $color_milestone;         // Colour of milestone lines
  var $color_today;             // Colour of current date line
  var $milestones = array();    // Milestones are stored and then drawn over the top of the tasks

  function init($options=array(), $tasks=array()) {
    global $graph_start_date, $graph_completion_date, $graph_type;
  
    $this->options = $options;
    $this->height = count($tasks) * ($this->bar_height + $this->task_padding) * 2 + $this->top_margin + $this->bottom_margin;

    get_date_range($tasks);

    // create image
    $this->image = imageCreate($this->width, $this->height);

    // 'Constant' colours for task types
    $this->task_colors = array(TT_TASK  => array("actual"=>imageColorAllocate($this->image, 0, 0, 255)
                                                ,"target"=>imageColorAllocate($this->image, 128, 128, 255))
                              ,TT_PHASE => array("actual"=>imageColorAllocate($this->image, 192, 0, 255)
                                                ,"target"=>imageColorAllocate($this->image, 192, 128, 255)));

    // allocate all required colors
    $this->color_background = imageColorAllocate($this->image, 255, 255, 255);
    $this->color_text = imageColorAllocate($this->image, 0, 0, 0);
    $this->color_grid = imageColorAllocate($this->image, 200, 200, 255);
    $this->color_milestone = imageColorAllocate($this->image, 255, 128, 255);
    $this->color_today = imageColorAllocate($this->image, 255, 192, 0);

    // clear the image space with the background color
    imageFilledRectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $this->color_background);

    // Draw the time range text
    #if ($graph_type == "phases") {
    #	$title = "Phase Graph for Period ";
    #} else {
    #	$title = "Task Graph for Period ";
    #}
    #$title .=  date("j/n/Y", get_date_stamp($graph_start_date))
    #		 . " to "
    #		 . date("j/n/Y", get_date_stamp($graph_completion_date));
    #imageString($this->image, 5, 3, 3, $title, $this->color_text);

    $this->y = $this->top_margin;
  }

  function draw_task($task, $show_children = true, $indent = 0) {
    $y = $this->y;              // Store y in local variable for quick access
    $y += $this->task_padding;

    // Text
    $text = $task->get_value("taskName");
    echo_debug("task: $text<br>");
    imageString($this->image, 3, 3 + $indent, $y, $text, $this->color_text);

    // Get date values
    $date_target_start = $task->get_value("dateTargetStart");
    if ($date_target_start == "0000-00-00")
      $date_target_start = "";
    $date_target_completion = $task->get_value("dateTargetCompletion");
    if ($date_target_completion == "0000-00-00")
      $date_target_completion = "";
    $date_actual_start = $task->get_value("dateActualStart");
    if ($date_actual_start == "0000-00-00")
      $date_actual_start = "";
    $date_actual_completion = $task->get_value("dateActualCompletion");
    if ($date_actual_completion == "0000-00-00")
      $date_actual_completion = "";

    // target bar
    $color = $this->task_colors[$task->get_value("taskTypeID")]["target"];
    $this->draw_dates($date_target_start, $date_target_completion, $y, $color, true);
    $y += $this->bar_height;

    // actual bar
    if ($date_actual_completion == "" && $date_actual_start != "" && $task->get_value("percentComplete") > 0) {
      // Task isn't complete but we can forecast comlpetion using percent complete and start date - show forecast
      $date_forecast_completion = date("Y-m-d", $task->get_forecast_completion());
      $color = $this->task_colors[$task->get_value("taskTypeID")]["actual"];
      $this->draw_dates($date_actual_start, $date_forecast_completion, $y, $color, false);      // Forecast bar
      $this->draw_dates($date_actual_start, date("Y-m-d"), $y, $color, true);   // Solid bar for already completed portion
    } else {
      // Just show dates as usual
      $color = $this->task_colors[$task->get_value("taskTypeID")]["actual"];
      $this->draw_dates($date_actual_start, $date_actual_completion, $y, $color, true);
    }
    $y += $this->bar_height;

    $y += $this->task_padding;
    // Grid line below current task
    imageLine($this->image, 0, $y, $this->width, $y, $this->color_grid);

    $this->y = $y;              // Store Y back in class variable for another time

    // Register milestones
    if ($task->get_value("taskTypeID") == TT_MILESTONE && ($date_target_completion || $date_actual_completion)) {
      if ($date_actual_completion) {
        $date_milestone = $date_actual_completion;
      } else {
        $date_milestone = $date_target_completion;
      }
      $this->register_milestone($date_milestone);
    }
    // Draw child tasks
    if ($show_children) {
      # This seems to take a very long time.. -alex
      #$filter = task::get_task_list_filter($this->options);
      #$children = task::get_task_children($filter);
      #reset($children);
      #while (list(, $child) = each($children)) {
        #$this->draw_task($child["object"], $show_children, $indent + $this->indent_increment);
      #}
    }
  }

  function register_milestone($date) {
    $this->milestones[] = $date;
  }

  function draw_milestones() {
    reset($this->milestones);
    while (list(, $milestone) = each($this->milestones)) {
      $x = $this->date_to_x($milestone);
      imageDashedLine($this->image, $x, $this->top_margin, $x, $this->height - $this->bottom_margin, $this->color_milestone);
      imageDashedLine($this->image, $x + 1, $this->top_margin, $x + 1, $this->height - $this->bottom_margin, $this->color_milestone);
    }
  }

  function draw_today() {
#$x = $this->date_stamp_to_x(mktime());
    $x = $this->date_to_x(date("Y-m-d"));
    imageDashedLine($this->image, $x, $this->top_margin, $x, $this->height - $this->bottom_margin, $this->color_today);
    imageDashedLine($this->image, $x + 1, $this->top_margin, $x + 1, $this->height - $this->bottom_margin, $this->color_today);
  }

  function draw_dates($date_start, $date_completion, $y, $color, $filled) {
    echo_debug("Drawing '$date_start' to '$date_completion'<br>");
    if ($date_start && $date_completion) {
      // Task is complete - show full bar
      echo_debug("Drawing date range<br>");
      $x_start = $this->date_to_x($date_start);
      $x_completion = $this->date_to_x($date_completion);
      $this->draw_rectangle($x_start, $y, $x_completion, $y + $this->bar_height, $color, $filled);
    } else if ($date_completion) {
      // We can only show the completion date - draw a triangle
      echo_debug("Drawing completion date<br>");
      $x_completion = $this->date_to_x($date_completion);
      echo_debug("Completion date x=$x_completion<br>");
      $this->draw_polygon(array($x_completion, $y, $x_completion, $y + $this->bar_height, $x_completion - 4, $y + 4), 3, $color, $filled);
    } else if ($date_start) {
      // We can only show the start date - draw a triangle
      echo_debug("Drawing start date<br>");
      $x_start = $this->date_to_x($date_start);
      $this->draw_polygon(array($x_start, $y, $x_start, $y + $this->bar_height, $x_start + 4, $y + 4), 3, $color, $filled);
    }
  }

  function draw_grid() {
    global $graph_start_date, $graph_completion_date;

    $start_stamp = get_date_stamp($graph_start_date);
    $completion_stamp = get_date_stamp($graph_completion_date);
    $graph_time_width = $completion_stamp - $start_stamp;

    // 7 Day increment
    $time_increment = mktime(0, 0, 0, 0, TICK_SIZE) - mktime(0, 0, 0, 0, 0);
    if ($time_increment * (MAX_TICKS + 1) < $graph_time_width) {
      $time_increment = mktime(0, 0, 0, 0, LARGE_TICK_SIZE) - mktime(0, 0, 0, 0, 0);
    }

    $current_stamp = $start_stamp;

    while ($current_stamp < $completion_stamp) {
      $x_pos = $this->date_stamp_to_x($current_stamp);
      imageLine($this->image, $x_pos, $this->top_margin - 5, $x_pos, $this->height - $this->bottom_margin, $this->color_grid);
      imageString($this->image, 3, $x_pos - 20, $this->top_margin - 20, date("d/m", $current_stamp), $this->color_text);
      $current_stamp += $time_increment;
    }

    // Horizontal line above tasks
    imageLine($this->image, 0, $this->top_margin, $this->width, $this->top_margin, $this->color_grid);
  }

  function draw_polygon($points, $num_points, $color, $filled) {
    if ($filled) {
      imageFilledPolygon($this->image, $points, $num_points, $color);
    } else {
      imagePolygon($this->image, $points, $num_points, $color);
    }
  }

  function draw_rectangle($x1, $y1, $x2, $y2, $color, $filled) {
    if ($filled) {
      imageFilledRectangle($this->image, $x1, $y1, $x2, $y2, $color);
    } else {
      imageRectangle($this->image, $x1, $y1, $x2, $y2, $color);
    }
  }

  function draw_legend_bar($x, $y, $text, $color, $filled) {
    $legend_bar_width = 30;
    $x2 = $x + $legend_bar_width;
    $this->draw_rectangle($x, $y, $x2, $y + 8, $color, $filled);
    $x = $x2 + $this->task_padding;
    imageString($this->image, 3, $x, $y - 2, $text, $this->color_text);
  }

  // If $start == true draws a starting triangle, otherwise draws an ending triangle
  function draw_legend_marker($x, $y, $text, $color, $start) {
    $legend_bar_width = 30;
    $x2 = $x + $legend_bar_width;
    if ($start) {
      $points = array($x, $y, $x, $y + 8, $x + 4, $y + 4);
    } else {
      $points = array($x + 4, $y, $x + 4, $y + 8, $x, $y + 4);
    }
    $this->draw_polygon($points, 3, $color, true);
    $x = $x2 + $this->task_padding;
    imageString($this->image, 3, $x, $y - 2, $text, $this->color_text);
  }

  function draw_legend() {
    $y = $this->y;              // Store y in local variable for quick access
    $left_x = 3;
    $center_x = $this->width / 2;

    $y = $this->height - $this->bottom_margin + 20;

    imageString($this->image, 5, $this->task_padding, $y, "Legend:", $this->color_text);
    $y += 20;

    $this->draw_legend_bar($left_x, $y, "Target task period", $this->task_colors[TT_TASK]["target"], true);
    $this->draw_legend_bar($center_x, $y, "Target phase period", $this->task_colors[TT_PHASE]["target"], true);
    $y += 12;

    $this->draw_legend_bar($left_x, $y, "Actual task period", $this->task_colors[TT_TASK]["actual"], true);
    $this->draw_legend_bar($center_x, $y, "Actual phase period", $this->task_colors[TT_PHASE]["actual"], true);
    $y += 12;

    $this->draw_legend_bar($left_x, $y, "Forecast task period", $this->task_colors[TT_TASK]["actual"], false);
    $this->draw_legend_bar($center_x, $y, "Forecast phase period", $this->task_colors[TT_PHASE]["actual"], false);
    $y += 12;

    $this->draw_legend_marker($left_x, $y, "Start date (completion date not known)", $this->task_colors[TT_TASK]["actual"], true);
    $y += 12;

    $this->draw_legend_marker($left_x, $y, "Completion date (start date not known)", $this->task_colors[TT_TASK]["actual"], false);
    $y += 12;

    $this->y = $y;              // Store Y back in class variable for another time
  }

  // Converts from a date string to an X coordinate
  function date_to_x($date) {
    echo_debug("Converting $date<br>");
    return $this->date_stamp_to_x(get_date_stamp($date));
  }

  // Converts from a unix time stamp to an X coordinate
  function date_stamp_to_x($date) {
    global $graph_start_date, $graph_completion_date;
    echo_debug("Converting ".date("Y-m-d", $date)."<br>");

    $graph_time_width = get_date_stamp($graph_completion_date) - get_date_stamp($graph_start_date);
    echo_debug("graph_time_width = ".get_date_stamp($graph_completion_date)." - ".get_date_stamp($graph_start_date)."<br>");
    echo_debug("graph_time_width=$graph_time_width<br>");
    $time_offset = $date - get_date_stamp($graph_start_date);
    echo_debug("time_offset=$time_offset<br>");
    $decimal_pos = $time_offset / $graph_time_width;
    echo_debug("decimal_pos=$decimal_pos<br>");
    $working_width = $this->width - $this->left_margin - $this->right_margin;
    $x_pos = $this->left_margin + $decimal_pos * $working_width;

    echo_debug("Returning $x_pos<br>");
    return $x_pos;
  }

  // Output the image
  function output() {

    if (ALLOC_GD_IMAGE_TYPE == "PNG") {
      header("Content-type: image/png");
      imagePNG($this->image);
    } else {
      header("Content-type: image/gif");
      imageGIF($this->image);
    }
  }
}

  function get_date_range($tasks=array()) {
    global $graph_start_date, $graph_completion_date;

    if (count($tasks) == 0) {
      image_die("No matching tasks");
    }

    $graph_start_date = "9999-00-00";
    $graph_completion_date = "0000-00-00";

    reset($tasks);
    while (list(, $task) = each($tasks)) {
      if ($task->get_value("dateTargetStart") != "" && $task->get_value("dateTargetStart") != "0000-00-00" && $task->get_value("dateTargetStart") < $graph_start_date) {
        $graph_start_date = $task->get_value("dateTargetStart");
  #echo "A: $graph_start_date<br>";
      }

      if ($task->get_value("dateTargetCompletion") > $graph_completion_date) {
        $graph_completion_date = $task->get_value("dateTargetCompletion");
      }

      if ($task->get_value("dateActualStart") != "" && $task->get_value("dateActualStart") != "0000-00-00" && $task->get_value("dateActualStart") < $graph_start_date) {
        $graph_start_date = $task->get_value("dateActualStart");
  #echo "B: $graph_start_date<br>";
      }

      if ($task->get_value("dateActualCompletion") > $graph_completion_date) {
        $graph_completion_date = $task->get_value("dateActualCompletion");
      }

      if (date("Y-m-d", $task->get_forecast_completion()) > $graph_completion_date) {
        $graph_completion_date = date("Y-m-d", $task->get_forecast_completion());
      }

    }

    if ($graph_start_date == "9999-00-00") {
      image_die("No tasks with a start date set");
    }

    if ($graph_completion_date == "0000-00-00") {
      image_die("No tasks with a completion date set");
    }
  }



?>
