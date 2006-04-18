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

define("NO_AUTH",true);
require_once("alloc.inc");

define("B","bad");
define("G","good");
define("H","help");

function e($type,$str) {
  global $TPL;
  echo "<br/><img src=\"".$TPL["url_alloc_images"]."icon_message_".$type.".gif\"/>";
  echo "&nbsp;&nbsp;".$str;
}

#echo phpinfo();

$lockfile = "INSTALLER_LOCK";

$lines = file($lockfile);
if ($lines[0] == "1") {
  echo "The installer has been disabled by the contents of the lockfile.";
  exit();
}

echo "<br/><h3>AllocPSA Installer</h3>";

// Check we aren't using an old version of PHP
$version = phpversion();
if (!version_compare($version, "4.3.0", ">=")) {
  e(B,"Must have PHP Version >= 4.3.0");
} else {
  e(G,"PHP Version ".$version. " ok.");
}

// Check that register globals is on
if (!ini_get('register_globals')) {
  e(B,"Must have register_globals enabled.");
} else {
  e(G,"Is enabled: register_globals");
}

// Check that we can determine a place to put alloc.inc
$include_path = ini_get('include_path');
if (!$include_path) {
  e(B,"Can't determine include_path!");
} else {
  e(G,"include_path: ".$include_path);
}

$memory_limit = ini_get('memory_limit');
if ($memory_limit < 64) {
  e(B,"memory_limit: ".$memory_limit." Must be at least 64 meg.");
} else {
  e(G,"memory_limit: ".$memory_limit);
}

// Check for GD // Determine PNG/GIF?

// Check attachments directory is set up
if (!defined("ATTACHMENTS_DIR")) {
  e(B,"Define a writeable directory as ATTACHMENTS_DIR in alloc.inc");
} else if (!ATTACHMENTS_DIR || !is_dir(ATTACHMENTS_DIR) || !is_writeable(ATTACHMENTS_DIR)) {
  e(B,"Ensure ATTACHMENTS_DIR: ".ATTACHMENTS_DIR." is writeable.");
} 

if (!is_dir(ATTACHMENTS_DIR."projects/")) { 
  e(B,"Please create directory: ".ATTACHMENTS_DIR."projects/");
}
if (!is_writeable(ATTACHMENTS_DIR."projects/")) {
  e(B,"Make this directory writeable by user httpd/apache: ".ATTACHMENTS_DIR."projects/");
}

if (!is_dir(ATTACHMENTS_DIR."clients/")) { 
  e(B,"Please create directory: ".ATTACHMENTS_DIR."clients/");
}
if (!is_writeable(ATTACHMENTS_DIR."clients/")) {
  e(B,"Make this directory writeable by user httpd/apache: ".ATTACHMENTS_DIR."clients/");
}




// Check that the directorys exist for client/project uploads

// Get path of alloc install

// generate alloc.inc (passwords usernames etc)

// determine php include path copy alloc.inc to it

// Get config info from alloc config/config.php


if (defined("INSTALLED") && INSTALLED) {
  $fp = fopen($lockfile,"w");
  fputs($fp,"1");
  fclose($fp);
}







?>
