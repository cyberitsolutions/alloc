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

global $TPL, $current_user;


// add a comment
$commentID = comment::add_comment($_REQUEST["entity"], $_REQUEST["entityID"], $_REQUEST["comment"], 
                                  $_REQUEST["commentMaster"], $_REQUEST["commentMasterID"]);

// add additional interested parties
if ($_REQUEST["eo_email"]) {
  $other_parties[$_REQUEST["eo_email"]] = array("name"       => $_REQUEST["eo_name"]
                                               ,"addIP"      => $_REQUEST["eo_add_interested_party"]
                                               ,"addContact" => $_REQUEST["eo_add_client_contact"]
                                               ,"clientID"   => $_REQUEST["eo_client_id"]);
}

// add all interested parties
$emailRecipients = comment::add_interested_parties($commentID, $_REQUEST["commentEmailRecipients"], $other_parties);

// If someone uploads an attachment
if ($_FILES) {
  comment::move_attachment("comment",$commentID);
}

// Attach any alloc generated timesheet pdf
if ($_REQUEST["attach_timeSheet"]) {
  comment::attach_timeSheet($commentID, $_REQUEST["entityID"], $_REQUEST["attach_timeSheet"]);
}

// Attach any alloc generated invoice pdf
if ($_REQUEST["attach_invoice"]) {
  comment::attach_invoice($commentID,$_REQUEST["entityID"],$_REQUEST["generate_pdf_verbose"]);
}

// Attach any alloc generated tasks pdf
if ($_REQUEST["attach_tasks"]) {
  comment::attach_tasks($commentID,$_REQUEST["entityID"],$_REQUEST["attach_tasks"]);
}


// Re-email the comment out, including any attachments
comment::send_comment($commentID,$emailRecipients);


// Re-direct browser back home
$TPL["message_good"][] = $message_good;
$extra.= "&sbs_link=comments";
alloc_redirect($TPL["url_alloc_".$_REQUEST["commentMaster"]].$_REQUEST["commentMaster"]."ID=".$_REQUEST["commentMasterID"].$extra);


?>
