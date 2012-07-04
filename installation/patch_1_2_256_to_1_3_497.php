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



/*

1. Fix config file from alloc.inc to alloc_config.php

2. mv/rename file upload directorys

3. Change database user permissions! so they can create/drop/alter

*/

// Path to alloc_config.php
define("ALLOC_CONFIG_PATH", realpath(dirname(__FILE__)."/..")."/alloc_config.php");


// Use action flags to specify which actions should be taken.
define("ACTION_RM_ALLOC_INC"                       ,1);
define("ACTION_CREATE_ALLOC_CONFIG"                ,2);
define("ACTION_MV_PROJECTS_DIR"                    ,3);
define("ACTION_MV_CLIENTS_DIR"                     ,4);
define("ACTION_MV_TASKS_DIR"                       ,5);
define("ACTION_ERR_ATTACHMENTS_DIR_NOT_DEFINED"    ,6);
define("ACTION_ERR_ATTACHMENTS_DIR_NOT_DIR"        ,7);
define("ACTION_ERR_ATTACHMENTS_DIR_NOT_WRITEABLE"  ,8);
define("ACTION_FIX_DB_USER_PERMS"                  ,9);
define("ACTION_CREATE_TABLE_PATCHLOG"              ,10);


// Get include_path. check for alloc.inc in include_path
$include_path = ini_get("include_path");

// Include path
$dirs = explode(PATH_SEPARATOR,$include_path);

if (is_array($dirs)) {
  foreach ($dirs as $d) {
    if (file_exists($d.DIRECTORY_SEPARATOR."alloc.inc")) {
      $old_alloc_inc = $d.DIRECTORY_SEPARATOR."alloc.inc";
    }
  }
}

if ($old_alloc_inc) {
  $file = $old_alloc_inc;
} else if (file_exists(ALLOC_CONFIG_PATH)) {
  $file = ALLOC_CONFIG_PATH;
} else {
  alloc_error("No config file found! Find the alloc.inc file and put it in the php include_path.",true);
}
  
$patterns = array("ALLOC_DB_NAME","ALLOC_DB_USER","ALLOC_DB_PASS","ALLOC_DB_HOST","ATTACHMENTS_DIR");
$lines = file($file);
foreach ($lines as $line) {
  foreach ($patterns as $pattern) {
    if (preg_match("/".$pattern."/",$line)) {
      preg_match("/^\s*define\(\"\w*\"\s*,\s*\"(.*)\"\)/",$line,$matches);
      $oldfile[$pattern] = $matches[1];
      !defined($pattern) && define($pattern,trim($matches[1]));
    }
  }
}

$newfile = array();


// Previous checks for upgrading from 1.2.256 
if (!file_exists(ALLOC_CONFIG_PATH) || filesize(ALLOC_CONFIG_PATH) <5) {
  
    foreach ($patterns as $pattern) {
      $newfile[] = "define(\"".$pattern."\",\"".$oldfile[$pattern]."\");";
    }
    $actions[] = ACTION_CREATE_ALLOC_CONFIG;
    $actions[] = ACTION_RM_ALLOC_INC;
}


!defined("ATTACHMENTS_DIR")     and $actions[] = ACTION_ERR_ATTACHMENTS_DIR_NOT_DEFINED;
!is_dir(ATTACHMENTS_DIR)        and $actions[] = ACTION_ERR_ATTACHMENTS_DIR_NOT_DIR;
!is_writable(ATTACHMENTS_DIR)   and $actions[] = ACTION_ERR_ATTACHMENTS_DIR_NOT_WRITEABLE;

is_dir(ATTACHMENTS_DIR."projects") && !is_dir(ATTACHMENTS_DIR."project") and $actions[] = ACTION_MV_PROJECTS_DIR;
is_dir(ATTACHMENTS_DIR."clients") && !is_dir(ATTACHMENTS_DIR."client")   and $actions[] = ACTION_MV_CLIENTS_DIR;
!is_dir(ATTACHMENTS_DIR."task")                                          and $actions[] = ACTION_MV_TASKS_DIR; // "tasks" didn't used to exist, so just mkdir


// Include the database connectivity classes
require_once("../shared/lib/db.inc.php");
require_once("../shared/lib/db_alloc.inc.php");

// Try and create a table to test if we have perm
$db = new db_alloc();
$db->verbose = 0; 
$q = "CREATE TABLE testCreateTableABC (heyID int)";
$db->query($q);
$cant_create = $db->get_error();

// Try and alter a table to test if we have perm
$db = new db_alloc();
$db->verbose = 0; 
$q = "ALTER TABLE testCreateTableABC ADD bee int";
$db->query($q);
$cant_alter = $db->get_error();

// Try and drop a table to test if we have perm
$db = new db_alloc();
$db->verbose = 0; 
$q = "DROP TABLE testCreateTableABC";
$db->query($q);
$cant_drop = $db->get_error();

// If we can't do all three, then update the users permissions
if ($cant_create || $cant_drop || $cant_alter) {
  $actions[] = ACTION_FIX_DB_USER_PERMS;
} 
$db->verbose = 1;


// Create table patchLog if it doesn't exist
if (!$db->table_exists("patchLog")) {
  $actions[] = ACTION_CREATE_TABLE_PATCHLOG;
}



$commands[ACTION_RM_ALLOC_INC]                       = "rm -f ".$old_alloc_inc;
$commands[ACTION_CREATE_ALLOC_CONFIG]                = "echo '<?php \n".implode("\n",$newfile)."\n?>' > ".ALLOC_CONFIG_PATH;
$commands[ACTION_MV_PROJECTS_DIR]                    = "mv ".ATTACHMENTS_DIR."projects ".ATTACHMENTS_DIR."project";
$commands[ACTION_MV_CLIENTS_DIR]                     = "mv ".ATTACHMENTS_DIR."clients ".ATTACHMENTS_DIR."client";
$commands[ACTION_MV_TASKS_DIR]                       = "mkdir ".ATTACHMENTS_DIR."task; chmod 777 ".ATTACHMENTS_DIR."task";
$commands[ACTION_ERR_ATTACHMENTS_DIR_NOT_DEFINED]    = "echo 'ERROR: No ATTACHMENTS_DIR defined'";
$commands[ACTION_ERR_ATTACHMENTS_DIR_NOT_DIR]        = "echo 'ERROR: ATTACHMENTS_DIR is not a directory: ".ATTACHMENTS_DIR."'";
$commands[ACTION_ERR_ATTACHMENTS_DIR_NOT_WRITEABLE]  = "echo 'ERROR: ATTACHMENTS_DIR is not webserver writeable: ".ATTACHMENTS_DIR."'";
$commands[ACTION_FIX_DB_USER_PERMS]                  = "mysql -u root mysql -e 'update db set Select_priv=\"y\",Select_priv=\"y\",Insert_priv=\"y\"\n";
$commands[ACTION_FIX_DB_USER_PERMS]                 .= ",Update_priv=\"y\",Delete_priv=\"y\",Create_priv=\"y\",Drop_priv=\"y\",References_priv=\"y\"\n";
$commands[ACTION_FIX_DB_USER_PERMS]                 .= ",Index_priv=\"y\",Alter_priv=\"y\" where User = \"".ALLOC_DB_USER."\"; flush privileges;'\n";

$commands[ACTION_CREATE_TABLE_PATCHLOG]              = "mysql -u root ".ALLOC_DB_NAME." -e 'CREATE TABLE patchLog ( patchLogID int(11) NOT NULL auto_increment, patchName varchar(255) NOT NULL,";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\npatchDesc text, patchDate timestamp(14) NOT NULL, PRIMARY KEY (patchLogID)) TYPE=ISAM PACK_KEYS=1;";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-1.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-2.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-3.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-4.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-5.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-6.sh\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-7.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-8.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "\nINSERT INTO patchLog (patchName,patchDesc,patchDate) VALUES (\"patch-9.sql\",\"\",\"2006-11-12 18:04:46\");";
$commands[ACTION_CREATE_TABLE_PATCHLOG]             .= "'";

$messages[ACTION_RM_ALLOC_INC] = "Please remove old config file: <b>".$old_alloc_inc."</b>";
$messages[ACTION_CREATE_ALLOC_CONFIG] = "Please create a file that is readable only by the webserver, here: <b>".ALLOC_CONFIG_PATH;
$messages[ACTION_CREATE_ALLOC_CONFIG].= "</b><br>The contents of the file should be:<br>";
$messages[ACTION_CREATE_ALLOC_CONFIG].= "<pre>&lt;?php <br>".implode("\n",$newfile)."\n?&gt;</pre>";
$messages[ACTION_CREATE_ALLOC_CONFIG].= "Ensure that that less-than symbol &lt; on the first line is the very first character in the file, ";
$messages[ACTION_CREATE_ALLOC_CONFIG].= "and that the greater-than symbol &gt; on the last line, is the absolute last character in the file.";
$messages[ACTION_MV_PROJECTS_DIR] = "Please rename ".ATTACHMENTS_DIR."projects  to  ".ATTACHMENTS_DIR."project";
$messages[ACTION_MV_CLIENTS_DIR] = "Please rename ".ATTACHMENTS_DIR."clients  to  ".ATTACHMENTS_DIR."client";
$messages[ACTION_MV_TASKS_DIR] = "Please create a webserver writeable directory: ".ATTACHMENTS_DIR."task";
$messages[ACTION_ERR_ATTACHMENTS_DIR_NOT_DEFINED] = "ERROR: No ATTACHMENTS_DIR defined";
$messages[ACTION_ERR_ATTACHMENTS_DIR_NOT_DIR] = "ERROR: ATTACHMENTS_DIR is not a directory: ".ATTACHMENTS_DIR;
$messages[ACTION_ERR_ATTACHMENTS_DIR_NOT_WRITEABLE] = "ERROR: ATTACHMENTS_DIR is not webserver writeable: ".ATTACHMENTS_DIR;
$messages[ACTION_FIX_DB_USER_PERMS] = "The database user <b>".ALLOC_DB_USER."</b> does not have the correct permissions required to operate the new patch system.";
$messages[ACTION_CREATE_TABLE_PATCHLOG] = "The patchLog table needs to be created.";

// If we're hitting this script with wget as part of the automatic livealloc upgrade process
// we just want to return the commands, so that the util/patch.sh script will eval them
if ($_GET["return_commands"] && is_array($actions) && count($actions)) {

  foreach ($actions as $action) {
    echo $commands[$action]."\n";
  }

// Else hitting this script with a web browser, provide more verbose instructions
} else if (is_array($actions) && count($actions)) {

  foreach ($actions as $action) {
    echo "<br><br> * ".$messages[$action];
    echo "<pre>Try the shell command:<br>".page::htmlentities($commands[$action])."</pre>";
  }

// Don't echo this for livealloc
} else if (!$_GET["return_commands"]) {
  echo "Please complete the upgrade by performing the <a href=\"patch.php\">database updates</a>.";
}






?>
