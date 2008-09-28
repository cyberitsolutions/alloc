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


define("DO_NOT_REDIRECT_TO_LOGIN",1);
require_once("../alloc.php");

// This script spits out a dynamically generated WSDL XML document, that enables
// easy to handle SOAP service definitions.

$wsdl = new WSDL_Gen('alloc_soap', config::get_config_item("allocURL").'soap/server.php', 'http://allocpsa.com/allocPSA');
$f = dirname(__FILE__)."/alloc.wsdl";
$handle = fopen($f,"w+");
fputs($handle,$wsdl->toXML());
fclose($handle);


?>
