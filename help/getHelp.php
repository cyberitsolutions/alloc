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

require_once("../alloc.php");

if ($_GET["topic"]) {

  $topic = $_GET["topic"];
  $TPL["str"] = "<div style='text-align:left'><table width='150' border='0' cellpadding='4' cellspacing='0' id='helper_table' class='helper_table' style='margin:40px'><tr><td>";
  $TPL["str"].= html_entity_decode(get_help_string($topic));
  $TPL["str"].= "</td></tr></table></div>";

} else {
  $TPL["str"] = "No valid help topic specified.";
}

include_template("templates/getHelpM.tpl");


?>
