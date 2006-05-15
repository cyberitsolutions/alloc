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

require_once("alloc.inc");
$elements = array(12, 25, 60, 120);
$element_titles = array("10p2d", "20p5d", "50p10d", "100p20d");
$complexities = array(10, 25, 50);
$complexity_titles = array("Small", "Medium", "Large");
$times = array(array(3, 4, 6), array(5, 7, 10), array(8, 10, 15), array(12, 15, 25));
$costs = array(array(6000, 10000, 17000), array(12000, 20000, 34000), array(30000, 50000, 100000), array(70000, 100000, 150000));

function makeEstimate() {
  global $TPL;
  global $times, $costs, $elements, $element_titles, $complexities, $complexity_titles;
  if ($_POST["pages"] && $_POST["databases"] && $_POST["complexity"] && $_POST["multiplier"]) {

    // figure out where we fit in table of set complexities
    $col_match = 0;
    $col = -1;
    for ($i = 0; $i < count($complexities) && $col < 0; $i++) {
      if ($_POST["complexity"] == $complexities[$i]) {
        $col_match = TRUE;
      }
      if ($_POST["complexity"] <= $complexities[$i]) {
        $col = $i;
      }
    }
    if ($col == -1) {
      $col = count($complexities);
    }
    // figure out where we fit in table of set pages+databases
    $row_match = 0;
    $row = -1;
    for ($i = 0; $i < count($elements) && $row < 0; $i++) {
      if (($_POST["pages"] + $_POST["databases"]) == $elements[$i]) {
        $row_match = TRUE;
      }
      if (($_POST["pages"] + $_POST["databases"]) <= $elements[$i]) {
        $row = $i;
      }
    }
    if ($row == -1) {
      $row = count($elements);
    }
    // make cost estimate
    if ($row_match == TRUE && $col_match == TRUE) {
      $cost = $costs[$row][$col];
      $time = $times[$row][$col];
    } else if ($row_match == TRUE) {
      $cost = (($costs[$row][$col] - $costs[$row][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($_POST["complexity"] - $complexities[$col - 1]) + $costs[$row][$col - 1];
      $time = (($times[$row][$col] - $times[$row][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($costsomplexity - $complexities[$col - 1]) + $times[$row][$col - 1];
    } else if ($col_match == 1) {
      $cost = (($costs[$row][$col] - $costs[$row - 1][$col]) / ($elements[$row] - $elements[$row - 1])) * ($_POST["databases"] + $_POST["pages"] - $elements[$row - 1]) + $costs[$row - 1][$col];
      $time = (($times[$row][$col] - $times[$row - 1][$col]) / ($elements[$row] - $elements[$row - 1])) * ($_POST["databases"] + $_POST["pages"] - $elements[$row - 1]) + $times[$row - 1][$col];
    } else {

      // merge row1
      $temp_cost1 = (($costs[$row][$col] - $costs[$row][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($costsomplexity - $complexities[$col - 1]) + $costs[$row][$col - 1];
      $temp_time1 = (($times[$row][$col] - $times[$row][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($costsomplexity - $complexities[$col - 1]) + $times[$row][$col - 1];

      // merge row2
      $temp_cost2 = (($costs[$row - 1][$col] - $costs[$row - 1][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($costsomplexity - $complexities[$col - 1]) + $costs[$row - 1][$col - 1];
      $temp_time2 = (($times[$row - 1][$col] - $times[$row - 1][$col - 1]) / ($complexities[$col] - $complexities[$col - 1])) * ($costsomplexity - $complexities[$col - 1]) + $times[$row - 1][$col - 1];

      // merge results
      $cost = (($temp_cost1 - $temp_cost2) / ($elements[$row] - $elements[$row - 1])) * ($_POST["databases"] + $_POST["pages"] - $elements[$row - 1]) + $temp_cost2;
      $time = (($temp_time1 - $temp_time2) / ($elements[$row] - $elements[$row - 1])) * ($_POST["databases"] + $_POST["pages"] - $elements[$row - 1]) + $temp_time2;
    }

    // print table
    for ($i = 0, $ince = 0; $i < count($elements) + 2 - $row_match; $i++) {
      print "<tr>\n";
      for ($j = 0, $incx = 0; $j < count($complexities) + 2 - $col_match; $j++) {

        // top headings
        if ($i == 0) {
          if ($j == 0) {
            print "  <td><b>Conplexity</b></td>\n";
          } else if ($j == $col + 1) {
            if ($col_match == 0) {
              $incx = 1;
              print "  <td bgcolor=\"#EEEEEE\"><b>This (".$_POST["complexity"].")</b></td>\n";
            } else {
              print "  <td bgcolor=\"#EEEEEE\"><b>".$complexity_titles[$j - 1 - $incx]." (".$costsomplexity.")</b></t>\n";
            }
          } else {
            print "  <td align=\"center\"><b>".$complexity_titles[$j - 1 - $incx]." (".$complexities[$j - 1 - $incx].")</b></t>\n";
          }
        }
        // side headings
        else if ($j == 0) {
          if ($i == $row + 1) {
            if ($row_match == 0) {
              $ince = 1;
            }
            print "  <td bgcolor=\"#EEEEEE\"><b>".$_POST["pages"]."p".$_POST["databases"]."d</b></td>\n";
          } else {
            print "  <td><b>".$element_titles[$i - 1 - $ince]."</b></td>\n";
          }
        }
        // highlighted row
        else if ($i == $row + 1 && $row_match == 0) {
          $ince = 1;
          if ($j == $col + 1) {
            print "<td bgcolor=\"#DDDDDD\"><b>".round($time * $_POST["multiplier"])."weeks<br>\n";
            print "$".round(($cost * $_POST["multiplier"]), -3)."</b></td>\n";
          } else {
            print "<td bgcolor=\"#EEEEEE\">&nbsp;</td>\n";
          }
        }
        // highlighted col (no data)
        else if ($j == $col + 1 && $col_match == 0) {
          $incx = 1;
          if ($i == $row + 1) {
            print "<td bgcolor=\"#DDDDDD\"><b>".round($time * $_POST["multiplier"])."weeks<br>\n";
            print "$".round(($cost * $_POST["multiplier"]), -3)."</b></td>\n";
          } else {
            print "<td bgcolor=\"#EEEEEE\">&nbsp;</td>\n";
          }
        }
        // all the rest
        else {
          if ($i == $row + 1 && $j == $col + 1) {
            print "<td bgcolor=\"#DDDDDD\">";
          } else if ($i == $row + 1 || $j == $col + 1) {
            print "<td bgcolor=\"#EEEEEE\">";
          } else {
            print "<td>";
          }
          if ($i == $row + 1 && $j == $col + 1) {
            print "<b>".round($time * $_POST["multiplier"])."weeks<br>\n";
            print "$".round(($cost * $_POST["multiplier"]), -3)."</b></td>\n";
          } else {
            print round($times[$i - 1 - $ince][$j - 1 - $incx] * $_POST["multiplier"])." weeks<br>\n";
            print "$".round(($costs[$i - 1 - $ince][$j - 1 - $incx] * $_POST["multiplier"]), -3)."</td>\n";
          }
        }
      }
      print "</tr>\n";
    }
  }
}

if ($_POST["multiplier"] && $_POST["multiplier"] == -1) {
  if (isset($custom)) {
    $_POST["multiplier"] = $custom;
  } else {
    unset($_POST["multiplier"]);
  }
}
$multiplier_options = array(0=>"-- Select One --", 3=>"Java Frontend Client/Server", 2=>"Java Servlet/JSP", 2=>"Java Servlet/PHP", 1.2=>"PHP/Oracle", 1=>"PHP/SQL", 1=>"Python Frontend Client/Server", -1=>"(Custom Multiplier)");
$TPL["multiplier_options"] = get_options_from_array($multiplier_options, "", true);
include_template("templates/costtime.tpl");



?>
