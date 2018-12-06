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

require_once("../alloc.php");

/*
 ClientName
 Phone Number
 Fax Number
 Postal Address
 Postal Suburb
 Postal State
 Postal Postcode
 Street Address
 Street Suburb
 Comment
 Main_Contact
 Main Contact Email
*/

$row = 1;
// NOTE:: what? you don't do name a file this way for a project. 😞 --
// cjbayliss, 2018-11
if (($handle = fopen("../../David_Clients.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        foreach ($data as $key => $val) {
            $data[$key] = utf8_encode($data[$key]);
        }

        unset($comment_id, $cc_id);
        $client = new client();
        $client->set_value("clientName", $data[0]);
        $client->set_value("clientPhoneOne", $data[1]);
        $client->set_value("clientFaxOne", $data[2]);
        $client->set_value("clientStreetAddressOne", $data[3]);
        $client->set_value("clientSuburbOne", $data[4]);
        $client->set_value("clientStateOne", $data[5]);
        $client->set_value("clientPostcodeOne", $data[6]);
        $client->set_value("clientStreetAddressTwo", $data[7]);
        $client->set_value("clientSuburbTwo", $data[8]);
        $client->set_value("clientStatus", "current");
        $client->set_value("clientModifiedUser", $current_user->get_id());
        $client->save();

        if ($client->get_id()) {
            if (rtrim($data[9])) {
                $comment = new comment();
                $comment->set_value("commentMaster", "client");
                $comment->set_value("commentMasterID", $client->get_id());
                $comment->set_value("commentType", "client");
                $comment->set_value("commentLinkID", $client->get_id());
                $comment->set_value("comment", $data[9]);
                $comment->save();
                $comment_id = $comment->get_id();
            }

            if ($data[10] || $data[11]) {
                $cc = new clientContact();
                $cc->set_value("clientID", $client->get_id());
                $cc->set_value("primaryContact", 1);
                $cc->set_value("clientContactName", $data[10]);
                $cc->set_value("clientContactEmail", $data[11]);
                $cc->save();
                $cc_id = $cc->get_id();
            }
        }
        $x++;
        echo "<br>".$client->get_id()." --- ".$cc_id." --- ".$comment_id;
        if ($x>4) {
            //die();
        }
    }
    fclose($handle);
}
