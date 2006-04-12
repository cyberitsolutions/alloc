<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */



require_once("alloc.inc");

// This file will get all te managerUserID from qall the projects and change the person Role to 
// Project Manager + Time Sheet Recipient for that project.

$q = "select managerUserID,projectID from project";

$db2 = new db_alloc;
$db = new db_alloc;

$db->query($q);

while ($db->next_record()) {

  $q = sprintf("update projectPerson set projectPersonRoleID = 3 WHERE personID = %d and projectID = %d"
        ,$db->f("managerUserID"),$db->f("projectID"));
  $db2->query($q);
  echo "yeah";
}




?>
