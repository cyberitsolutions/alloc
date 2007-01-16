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

global $sess, $TPL;

$historyID = $_POST["historyID"] or $historyID = $_GET["historyID"];

if ($historyID) {
  if (is_numeric($historyID)) {
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM history WHERE historyID = %d", $historyID);
    $db->query($query);
    $db->next_record();
    header("Location: ".$sess->url($TPL[$db->f("the_place")]."historyID=".$historyID).$db->f("the_args"));
  } else {
    header("Location: ".$sess->url($historyID));
  }
}









?>
