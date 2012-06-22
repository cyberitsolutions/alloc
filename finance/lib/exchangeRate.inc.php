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

class exchangeRate extends db_entity {
  public $data_table = "exchangeRate";
  public $display_field_name = "exchangeRate";
  public $key_field = "exchangeRateID";
  public $data_fields = array("exchangeRateCreatedDate"
                             ,"exchangeRateCreatedTime"
                             ,"fromCurrency"
                             ,"toCurrency"
                             ,"exchangeRate"
                             );

  function get_er($from, $to, $date="") {
    static $cache;
    if (imp($cache[$from][$to][$date])) {
      return $cache[$from][$to][$date];
    }
    $db = new db_alloc();
    if ($date) {
      $q = prepare("SELECT *
                      FROM exchangeRate 
                     WHERE exchangeRateCreatedDate = '%s'
                       AND fromCurrency = '%s'
                       AND toCurrency = '%s'
                   ",$date
                    ,$from
                    ,$to
                  );
      $db->query($q);
      $row = $db->row();
    } 

    if (!$row) {
      $q = prepare("SELECT *
                      FROM exchangeRate 
                     WHERE fromCurrency = '%s'
                       AND toCurrency = '%s'
                  ORDER BY exchangeRateCreatedTime DESC
                     LIMIT 1
                   ",$from
                    ,$to
                  );
      $db->query($q);
      $row = $db->row();
    }
    $cache[$from][$to][$date] = $row["exchangeRate"];
    return $row["exchangeRate"];
  }

  function convert($currency, $amount, $destCurrency=false, $date=false, $format="%m") {
    $date or $date = date("Y-m-d");
    $destCurrency or $destCurrency = config::get_config_item("currency");
    $er = exchangeRate::get_er($currency,$destCurrency,$date);
    return page::money($destCurrency,$amount*$er,$format);
  }

  function update_rate($from, $to) {
    $rate = get_exchange_rate($from,$to);
    if ($rate) {
      $er = new exchangeRate();
      $er->set_value("exchangeRateCreatedDate",date("Y-m-d"));
      $er->set_value("fromCurrency",$from);
      $er->set_value("toCurrency",$to);
      $er->set_value("exchangeRate",$rate);
      $er->save();
      return $from." -> ".$to.":".$rate." ";
    } else {
      echo date("Y-m-d H:i:s")."Unable to obtain exchange rate information for ".$from." to ".$to."!";
    }
  }

  function download() {
    // Get default currency
    $default_currency = config::get_config_item("currency");

    // Get list of active currencies
    $meta = new meta("currencyType");
    $currencies = $meta->get_list();

    foreach ((array)$currencies as $code => $currency) {
      if ($code == $default_currency)
      	continue;
      if ($ret = exchangeRate::update_rate($code, $default_currency))
      	$rtn []= $ret;
      if ($ret = exchangeRate::update_rate($default_currency, $code))
      	$rtn []= $ret;

    }
    return $rtn;
  }

}

?>
