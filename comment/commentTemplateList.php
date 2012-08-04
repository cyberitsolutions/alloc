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

function show_commentTemplate($template_name) {
  global $TPL;
  
// Run query and loop through the records
  $db = new db_alloc();
  $query = "SELECT * FROM commentTemplate ORDER BY commentTemplateType, commentTemplateName";
  $db->query($query);
  while ($db->next_record()) {
    $commentTemplate = new commentTemplate();
    $commentTemplate->read_db_record($db);
    $commentTemplate->set_values();
    $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";
    include_template($template_name);
  }
}

$TPL["main_alloc_title"] = "Comment Template List - ".APPLICATION_NAME;
include_template("templates/commentTemplateListM.tpl");

?>
