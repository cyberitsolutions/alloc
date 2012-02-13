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


require_once(dirname(__FILE__)."/task.inc.php");
require_once(dirname(__FILE__)."/task_calendar.inc.php");
require_once(dirname(__FILE__)."/task_calendar_home_item.inc.php");
require_once(dirname(__FILE__)."/top_ten_tasks_home_item.inc.php");
require_once(dirname(__FILE__)."/task_message_list_home_item.inc.php");
require_once(dirname(__FILE__)."/task_list_print.inc.php");

class task_module extends module
{
  var $db_entities = array("task");
  var $home_items = array("task_calendar_home_item","top_ten_tasks_home_item","task_message_list_home_item");
}

?>
