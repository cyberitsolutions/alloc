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

function show_taskCommentTemplate($template_name) {
  global $TPL;
  
// Run query and loop through the records
  $db = new db_alloc;
  $query = "SELECT * FROM taskCommentTemplate $where ORDER BY taskCommentTemplateName";
  $db->query($query);
  while ($db->next_record()) {
    $taskCommentTemplate = new taskCommentTemplate;
    $taskCommentTemplate->read_db_record($db);
    $taskCommentTemplate->set_tpl_values(DST_HTML_ATTRIBUTE, "TCT_");
    $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";
    include_template($template_name);
  }
}

include_template("templates/taskCommentTemplateListM.tpl");
page_close();
