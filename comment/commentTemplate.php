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

// Create an object to hold a commentTemplate
$commentTemplate = new commentTemplate();

// Load the commentTemplate from the database

$commentTemplateID = $_POST["commentTemplateID"] or $commentTemplateID = $_GET["commentTemplateID"];

if ($commentTemplateID){
 $commentTemplate->set_id($commentTemplateID);
 $commentTemplate->select();
}

// Process submission of the form using the save button
if ($_POST["save"]) {
  $commentTemplate->read_globals();
  $commentTemplate->save();
  alloc_redirect($TPL["url_alloc_commentTemplateList"]);

// Process submission of the form using the delete button
} else if ($_POST["delete"]) {
  $commentTemplate->delete();
  alloc_redirect($TPL["url_alloc_commentTemplateList"]);
  exit();
}
// Load data for display in the template
$commentTemplate->set_values();

$ops = array(""=>"Comment Template Type","task"=>"Task","timeSheet"=>"Time Sheet","project"=>"Project"
            ,"client"=>"Client", "invoice"=>"Invoice","productSale"=>"Sale");
$TPL["commentTemplateTypeOptions"] = page::select_options($ops,$commentTemplate->get_value("commentTemplateType"));

$TPL["main_alloc_title"] = "Edit Comment Template - ".APPLICATION_NAME;
// Invoke the page's main template
include_template("templates/commentTemplateM.tpl");

?>

