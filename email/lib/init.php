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


require_once(dirname(__FILE__)."/token.inc.php");
require_once(dirname(__FILE__)."/tokenAction.inc.php");
require_once(dirname(__FILE__)."/email.inc.php");
require_once(dirname(__FILE__)."/email_receive.inc.php");
require_once(dirname(__FILE__)."/sentEmailLog.inc.php");
require_once(dirname(__FILE__)."/command.inc.php");
require_once(dirname(__FILE__)."/mimeDecode.php");


class email_module extends module {
  var $db_entities = array("token");
}

?>
