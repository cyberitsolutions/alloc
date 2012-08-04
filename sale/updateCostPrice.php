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

define("NO_REDIRECT",1);
require_once("../alloc.php");

$db = new db_alloc();

$product = $_GET["product"];
$quantity = $_GET["quantity"];

$p= new product();
$p->set_id($product);
$p->select();
$p->set_tpl_values();

// Probably not valid XML, but jQuery will parse it.
echo "<data>\n";
echo "<price>".page::money($TPL["sellPriceCurrencyTypeID"],$TPL["sellPrice"]*$quantity,"%m")."</price>\n";
echo "<priceCurrency>".$TPL["sellPriceCurrencyTypeID"]."</priceCurrency>\n";
echo "<priceTax>".($TPL["sellPriceIncTax"] ? "1" : "")."</priceTax>\n";
echo "<description>".$TPL["description"]."</description>\n";
echo "</data>\n";

?>
