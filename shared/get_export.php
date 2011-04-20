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

// Get an exported representation of something (at the moment, only a project)
// Call as: get_export.php?entity=project&id=1&format=planner

require_once("../alloc.php");

if (isset($_GET["id"]) && isset($_GET["entity"])) {
  switch($_GET["entity"]) {
    case "project":
    switch($_GET["format"]) {
      case "planner":
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="allocProject.planner"');
        echo export_gnome_planner(intval($_GET["id"]));
      break;
      case "csv":
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="allocProject.csv"');
        echo export_csv(intval($_GET["id"]));
    }
    break;
  }
}
