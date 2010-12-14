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

require_once("../alloc.php");

// Get default currency
$default_currency = config::get_config_item("currency");

// Get list of active currencies
$meta = new meta("currencyType");
$currencies = $meta->get_list();

foreach ((array)$currencies as $code => $currency) {
  $rate = get_exchange_rate($code,$default_currency);
  if ($rate) {
    $er = new exchangeRate();
    $er->set_value("exchangeRateCreatedDate",date("Y-m-d"));
    $er->set_value("fromCurrency",$code);
    $er->set_value("toCurrency",$default_currency);
    $er->set_value("exchangeRate",$rate);
    $er->save();
  } else {
    echo date("Y-m-d H:i:s")."Unable to obtain exchange rate information for ".$code." to ".$default_currency."!";
  }
}

?>
