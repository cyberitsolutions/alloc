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

if ($_POST['import']) {
  $projectID = $_POST["projectID"];
  $result = import_csv($_POST["filename"], $_POST["columns"], $_POST["headerRow"]);
  $TPL['result'] = $result;
  $TPL['main_alloc_title'] = "CSV Import Results";
  $TPL['projectID'] = $projectID;
  include_template("templates/csvImportResultM.tpl");
  exit;
}

//$_GET["filename"] = "/var/local/alloc/tmp/test.csv";
$basepath = ATTACHMENTS_DIR.'tmp'.DIRECTORY_SEPARATOR;
$rp = realpath($basepath.$_GET['filename']);
if ($rp === FALSE || strpos($rp, $basepath) !== 0)
  alloc_error("Illegal path",true);

$fh = fopen($rp, "r");
if ($fh === false) {
  alloc_error("File won't open.",true);
}

$rows = array();

$header = fgetcsv($fh);
$rows []= $header;

// only displaying the first 3 rows so the user can assign fields
for ($i = 0;$i < 2;$i++) {
  $rows []= fgetcsv($fh);
}

$columns = array();

// see if it's possible to make sense of the header row
if (in_array("name", $header)) {
  // official-ish header row, try to pre-parse it
  $TPL["headerRow"] = 'checked="checked"';
} else {
  $header = array();
}

$options = array('ignore' => 'Ignore',
  'name' => 'Task name',
  'description' => 'Task description',
  'manager' => 'Task manager',
  'assignee' => 'Task assignee',
  'limit' => 'Time limit (hours)',
  'timeBest' => 'Best-case estimate (hours)',
  'timeExpected' => 'Expected estimate (hours)',
  'timeWorst' => 'Worst-case estimate (hours)',
  'startDate' => 'Estimated start date',
  'completionDate' => 'Estimated completion date',
  'interestedParties' => 'Interested parties');

// there are 10 available fields, so max at 11 available rows
// Each row is <dropdown> <data> <data> <data>

$TPL["rows"] = array();
for ($i = 0; $i < min(11, count($rows[0]));$i++) {
  $TPL["rows"] []= array(
    'name' => "row_$i",
    'dropdown' => page::select_options($options, $header[$i]),
    'cols' => array($rows[0][$i], $rows[1][$i], $rows[2][$i])
  );
}

$TPL['message_help'] = "Use the dropdowns to indicate how each column should be interpreted.";

$TPL['filename'] = $_GET['filename'];
$TPL['projectID'] = $_GET['projectID'];
$TPL['main_alloc_title'] = "Process CSV upload";
include_template("templates/csvImportM.tpl");

?>

