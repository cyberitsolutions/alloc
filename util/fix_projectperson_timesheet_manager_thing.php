<?php


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
