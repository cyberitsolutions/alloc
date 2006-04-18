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

/*******************************************************************\
| This is quick and dirty client upload for conz potential clients	|
| and is expecting file format to look like this:					|
| (note double spaces around suburb).								|
| 																	|
| Southbank Finance													|
| 560 Church St  Richmond  (03) 9429-8611							|
|																	|
| Accent Investment Services										|
| Lvl 7,160 Queen St  Melbourne  (03) 9606-3333						|
|																	|
| Equity Trustees													|	
| Lvl 2,575 Bourke St  Melbourne  (03) 8623-5000					|
|																	|
\*******************************************************************/
define("NO_AUTH",true);
require_once("alloc.inc");



if ($upload) {
  $db = new db_alloc;

  is_uploaded_file($wages_file) || die("File referred to was not an uploaded file");
  $lines = file($wages_file);
  $a = 0;
  foreach($lines as $line) {

    if ($line && $line != "\n" && eregi("[a-z0-9]+", $line)) {
      $a++;

      if ($a % 2 != 0) {
        $clientName = trim(addslashes($line));
        $print = false;
      } else {
        $fields = explode("  ", $line);
        $clientStreetAddressOne = trim(addslashes($fields[0]));
        $clientSuburbOne = trim(addslashes($fields[1]));
        $clientPhoneOne = trim(addslashes($fields[2]));
        $print = true;
      }

      // print/save every second time 
      if ($print) {

        $db = new db_alloc;
        $db->query("select * from client where clientName = '".addslashes($clientName)."'");
        if (!$db->next_record()) {
          echo "<br><br><br>".$clientName;
          echo "<br>".$clientStreetAddressOne."<br>".$clientSuburbOne."<br>".$clientPhoneOne;
          $client = new client;
          $client->set_value("clientName", $clientName);
          $client->set_value("clientModifiedUser", 60);
          $client->set_value("clientStatus", "potential");
          $client->set_value("clientStreetAddressTwo", $clientStreetAddressOne);
          $client->set_value("clientSuburbTwo", $clientSuburbOne);
          $client->set_value("clientPhoneOne", $clientPhoneOne);
          $client->save();
        } else {
          echo "<br>Skipped this one, it already exists: ".$clientName;
          // echo "<br>" .$clientStreetAddressOne."<br>".$clientSuburbOne."<br>".$clientPhoneOne;
        }
      }



    }


  }
}




?><html> <head> </head> <body> <form action = '<?php echo $PHP_SELF ?>' method = "post" enctype = "multipart/form-data"> <input type = "file" name = "wages_file"> <input type = "submit" name = "upload"> </form> </body> </html>
