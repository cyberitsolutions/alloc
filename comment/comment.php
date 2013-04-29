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

global $TPL;
$current_user = &singleton("current_user");


// add a comment
$commentID = comment::add_comment($_REQUEST["entity"], $_REQUEST["entityID"], $_REQUEST["comment"], 
                                  $_REQUEST["commentMaster"], $_REQUEST["commentMasterID"]);

if (!$commentID) {
  alloc_error("Could not create comment.",1);
}

// add additional interested parties
if ($_REQUEST["eo_email"]) {
  $other_parties[$_REQUEST["eo_email"]] = array("name"       => $_REQUEST["eo_name"]
                                               ,"addIP"      => $_REQUEST["eo_add_interested_party"]
                                               ,"addContact" => $_REQUEST["eo_add_client_contact"]
                                               ,"clientID"   => $_REQUEST["eo_client_id"]);
}

// add all interested parties
$emailRecipients = comment::add_interested_parties($commentID, $_REQUEST["commentEmailRecipients"], $other_parties);

// We're going to store all the attachments and generated pdf files in this array
$files = array();

// If someone uploads attachments
if ($_FILES) {
  $files = rejig_files_array($_FILES);
}

// Attach any alloc generated timesheet pdf
if ($_REQUEST["attach_timeSheet"]) {
  $files[] = comment::attach_timeSheet($commentID, $_REQUEST["entityID"], $_REQUEST["attach_timeSheet"]);
}

// Attach any alloc generated invoice pdf
if ($_REQUEST["attach_invoice"]) {
  $_REQUEST["attach_invoice"] == $_REQUEST["generate_pdf_verbose"] and $verbose = true; // select
  $_REQUEST["generate_pdf_verbose"] and $verbose = true; // link
  $files[] = comment::attach_invoice($commentID,$_REQUEST["entityID"],$verbose);
}

// Attach any alloc generated tasks pdf
if ($_REQUEST["attach_tasks"]) {
  $files[] = comment::attach_tasks($commentID,$_REQUEST["entityID"],$_REQUEST["attach_tasks"]);
}

// Store the files on the file-system temporarily in this dir
$dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$commentID;
if (!is_dir($dir)) {
  mkdir($dir, 0777);
}

// Write out all of the attachments and generated files to the local filesystem
foreach ((array)$files as $k => $f) {
  $fullpath = $dir.DIRECTORY_SEPARATOR.$f["name"];
  if ($f["blob"]) {
    file_put_contents($fullpath,$f["blob"]);
  } else if ($f["tmp_name"]) {
    rename($f["tmp_name"],$fullpath);
  }
  $files[$k]["fullpath"] = $fullpath;
}

if ($files) {
  comment::update_mime_parts($commentID, $files);
}

// Re-email the comment out, including any attachments
if (!comment::send_comment($commentID,$emailRecipients,false,$files)) {
  alloc_error("Email failed to send.");
}

foreach ((array)$files as $k => $f) {
  if (file_exists($f["fullpath"])) {
    unlink($f["fullpath"]);
  }
}
rmdir_if_empty($dir);



// Re-direct browser back home
$TPL["message_good"][] = $message_good;
$extra.= "&sbs_link=comments";
alloc_redirect($TPL["url_alloc_".$_REQUEST["commentMaster"]].$_REQUEST["commentMaster"]."ID=".$_REQUEST["commentMasterID"].$extra);


?>
