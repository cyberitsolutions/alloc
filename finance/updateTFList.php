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


define("NO_REDIRECT",1);
require_once("../alloc.php");

if ($_GET["projectID"]) {
  usleep(300000);
  $project = new project();
  $project->set_id($_GET["projectID"]);
  $project->select();
  $tf_sel = $project->get_value("cost_centre_tfID") or $tf_sel = config::get_config_item("mainTfID");
  $tf = new tf();
  $options = page::select_options($tf->get_assoc_array("tfID","tfName"),$tf_sel);
  echo "<select id=\"tfID\" name=\"tfID\">".$options."</select>";
}


?>
