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

function get_help_button($topic, $module = "") {
  global $sess, $TPL;

  if ($module == "") {
    $module = ALLOC_CURRENT_MODULE;
  }

  if ($topic) {
    $url = $sess->url("../help/help.php?topic=$topic&module=$module");
  } else {
    $url = "../help/alloc_help.html#".$TPL["alloc_help_link_name"];
  }
  return "<a href=\"$url\" target=\"_blank\"><img src=\"../images/help.gif\" alt=\"help\" border=\"0\"></a>";
}

function help_button($topic = "", $module = "") {
  echo get_help_button($topic, $module);
}

function get_help_link() {
  global $TPL;
  $url = "../help/alloc_help.html#".$TPL["alloc_help_link_name"];
  echo "<a href=\"".$url."\">Help</a>";
}




?>
