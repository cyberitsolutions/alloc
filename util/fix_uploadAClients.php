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


$f = array(
"clientName1" => 0,
"clientName2" => 1,
"clientContactOther1" => 2,
"clientContactOther2" => 3,
"clientContactOther3" => 4,
"clientStreetAddressOne1" => 5,
"clientStreetAddressOne2" => 6,
"clientStreetAddressOne3" => 7,
"clientStreetAddressOne4" => 8,
"clientStreetAddressOne5" => 9,
"clientStateOne" => 10,
"clientPostcodeOne" => 11,
"clientCountryOne" => 12,
"clientPhoneOne" => 13,
"" => 14,
"" => 15,
"clientFaxOne" => 16,
"clientContactEmail" => 17,
"" =>18,
"clientContactName" => 19,
""=>20



);

$db = new db_alloc;

$file = file("./AClients.csv");

foreach ($file as $line) {

  $b = explode(",",$line);

  $clientName = $b[$f["clientName1"]];
  $b[$f["clientName2"]] and $clientName.= " ".$b[$f["clientName2"]];


  $clientStreetAddressOne = $b[$f["clientStreetAddressOne1"]];
  $b[$f["clientStreetAddressOne2"]] and $clientStreetAddressOne.= "\n".$b[$f["clientStreetAddressOne2"]];
  $b[$f["clientStreetAddressOne3"]] and $clientStreetAddressOne.= "\n".$b[$f["clientStreetAddressOne3"]];
  $b[$f["clientStreetAddressOne4"]] and $clientStreetAddressOne.= "\n".$b[$f["clientStreetAddressOne4"]];
  $b[$f["clientStreetAddressOne5"]] and $clientStreetAddressOne.= "\n".$b[$f["clientStreetAddressOne5"]];

  $clientStateOne = $b[$f["clientStateOne"]];
  $clientPostcodeOne = $b[$f["clientPostcodeOne"]];
  $clientCountryOne = $b[$f["clientCountryOne"]];
  $clientPhoneOne = $b[$f["clientPhoneOne"]];
  $clientFaxOne = $b[$f["clientFaxOne"]];

  $q = "INSERT INTO client (clientName, clientStreetAddressOne,clientStateOne,clientPostcodeOne,clientCountryOne,clientPhoneOne,clientFaxOne) VALUES ";
  $q.= sprintf("('%s','%s','%s','%s','%s','%s','%s')
               ",addslashes(str_replace("\"","",$clientName))
                ,addslashes(str_replace("\"","",$clientStreetAddressOne))
                ,addslashes(str_replace("\"","",$clientStateOne))
                ,addslashes(str_replace("\"","",$clientPostcodeOne))
                ,addslashes(str_replace("\"","",$clientCountryOne))
                ,addslashes(str_replace("\"","",$clientPhoneOne))
                ,addslashes(str_replace("\"","",$clientFaxOne)));

  $db->query($q);
  echo "<br><br>".$q;

	$db->query("select max(clientID) as cid from client");
	$db->next_record();
    $cid = $db->f("cid");

  $b[$f["clientContactOther1"]] and $clientContactOther = "Card ID: ".$b[$f["clientContactOther1"]];
  $b[$f["clientContactOther2"]] and $clientContactOther.= "\nCard Status: ".$b[$f["clientContactOther2"]];
  $b[$f["clientContactOther3"]] and $clientContactOther.= "\n".$b[$f["clientContactOther3"]];


  if ($d[$f["clientContactName"]] || $d[$f["clientContactEmail"]] || $clientContactOther) {
  
    $q = "SELECT max(clientID) AS clientID FROM client";
    $db->query($q);
    $db->next_record();
    $clientID = $db->f("clientID");
    $clientContactName = $d[$f["clientContactName"]] or $clientContactName = $clientName;
    $clientContactEmail = $d[$f["clientContactEmail"]];

    $q = "INSERT INTO clientContact (clientID,clientContactName,clientContactEmail,clientContactOther,clientContactPhone) VALUES ";
    $q.= sprintf("('%s','%s','%s','%s','%s')
                 ",addslashes(str_replace("\"","",$clientID))
                  ,addslashes(str_replace("\"","",$clientContactName))
                  ,addslashes(str_replace("\"","",$clientContactEmail))
                  ,addslashes(str_replace("\"","",$clientContactOther))
                  ,addslashes(str_replace("\"","",$clientPhoneOne))
                 );

    $db->query($q);
    echo "<br>".$q;
		
	$db->query("select max(clientContactID) as ccid FROM clientContact");
 	$db->next_record();
	$ccid = $db->f("ccid");


	$db->query("update client set clientPrimaryContactID = ".$ccid." WHERE clientID = ".$cid);


  }



}








?>
