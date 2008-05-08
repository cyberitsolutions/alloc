<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

Alex you need to write this script!!!

And as part of the upgrade path from alloc_dev to alloc_stage I run a
query on the db which says for each project: if there is an amount greater
than zero in the "Customer Billed At" and there is a timesheet manager for
that project, add a zero percent line item in the commission box for that
manager.
*/
require_once("alloc.php");

function get_preferred_tfID($personID) {

$p = new person;
$p->set_id($personID);
$p->select();
return $p->get_value("preferred_tfID");

}

$q= "select project.*, projectPerson.personID as pid, roleHandle from project
    left join projectPerson on projectPerson.projectID = project.projectID
    left join role on role.roleID = projectPerson.roleID";
$db = new db_alloc;
$db->query($q);

$db2 = new db_alloc;

while ($db->next_record()) {


if ($db->f("customerBilledDollars")>0 && $db->f("roleHandle") == "timeSheetRecipient") {

  if ($db->f("projectID") && $db->f("pid")) {
 
    $tfID = get_preferred_tfID($db->f("pid"));
  
    if ($tfID) { 

      #echo  "<br/>".person::get_fullname($db->f("pid"))." : ".get_tf_name($tfID);

      echo "<br/>".$db->f("projectName")." --- ". person::get_fullname($db->f("pid"));
#. " ".$db->f("roleHandle")." ". $db->f("customerBilledDollars");
      $q = sprintf("insert into projectCommissionPerson (projectID, personID,commissionPercent,tfID) values (%d,%d,0,%d)",$db->f("projectID"),$db->f("pid"),$tfID);
      #$db2->query($q);
      #echo "<br/>q: ".$q;

    } else { 
      echo "NO tfID for ".$db->f("pid");
    }
 
  }

  

}



}


?>
