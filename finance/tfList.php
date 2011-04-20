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

$current_user->check_employee();

if ($_REQUEST["owner"]) {
  $TPL["owner_checked"] = " checked";
} else {
  $TPL["owner_checked"] = "";
}

if ($_REQUEST["showall"]) {
  $TPL["showall_checked"] = " checked";
}

$TPL["main_alloc_title"] = "TF List - ".APPLICATION_NAME;

$TPL["tfListRows"] = tf::get_list($_REQUEST);


include_template("templates/tfListM.tpl");

?>
