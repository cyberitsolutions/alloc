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


require_once(ALLOC_MOD_DIR."/shared/lib/template.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/help.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/db_utils.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_db.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_db_alloc.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_session.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_home_item.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_toolbar_item.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_db_field.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_db_entity.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_module.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_event.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_sentEmailLog.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_alloc_email.inc.php");
require_once(ALLOC_MOD_DIR."/shared/lib/class_alloc_cache.inc.php");


class shared_module extends module
{
  var $db_entities = array("sentEmailLog");

  function register_toolbar_items() {
  }
  function register_home_items() {
  }
}




?>
