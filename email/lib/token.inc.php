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

class token extends db_entity {
  public $classname = "token";
  public $data_table = "token";
  public $key_field = "tokenID";
  public $data_fields = array("tokenHash"
                             ,"tokenEntity"
                             ,"tokenEntityID"
                             ,"tokenActionID"
                             ,"tokenExpirationDate"
                             ,"tokenUsed"
                             ,"tokenMaxUsed"
                             ,"tokenActive"
                             ,"tokenCreatedBy"
                             ,"tokenCreatedDate"
                             );


  function set_hash($hash,$validate=true) {
    
    $validate and $extra = " AND tokenActive = 1";
    $validate and $extra.= " AND (tokenUsed < tokenMaxUsed OR tokenMaxUsed IS NULL OR tokenMaxUsed = 0)";
    $validate and $extra.= prepare(" AND (tokenExpirationDate > '%s' OR tokenExpirationDate IS NULL)",date("Y-m-d H:i:s"));
    

    $q = prepare("SELECT * FROM token 
                   WHERE tokenHash = '%s'
                  $extra
                 ",$hash);
    #echo "<br><br>".$q;
    $db = new db_alloc();
    $db->query($q);
    if ($db->next_record()) {
      $this->set_id($db->f("tokenID"));
      $this->select();
      return true;
    }
  }

  function execute() {
    if ($this->get_id()) {
      if ($this->get_value("tokenActionID")) {
        $tokenAction = new tokenAction();
        $tokenAction->set_id($this->get_value("tokenActionID"));    
        $tokenAction->select();
      }
      if ($this->get_value("tokenEntity")) {
        $class = $this->get_value("tokenEntity");
        $entity = new $class;
        if ($this->get_value("tokenEntityID")) {
          $entity->set_id($this->get_value("tokenEntityID"));
          $entity->select();
        }
        $method = $tokenAction->get_value("tokenActionMethod");
        $this->increment_tokenUsed(); 
        if ($entity->get_id()) {
          return array($entity,$method);
        }
      }
    }
    return array(false,false);
  }

  function increment_tokenUsed() {
    $q = prepare("UPDATE token SET tokenUsed = coalesce(tokenUsed,0) + 1 WHERE tokenID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
  }

  function decrement_tokenUsed() {
    $q = prepare("UPDATE token SET tokenUsed = coalesce(tokenUsed,0) - 1 WHERE tokenID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
  }

  function get_hash_str() {
    list($usec, $sec) = explode(' ', microtime());
    $seed = $sec + ($usec * 100000);
    mt_srand($seed);
    $randval = mt_rand(1,99999999); // get a random 8 digit number
    $randval = sprintf("%-08d",$randval);
    $randval = base_convert($randval,10,36);
    return $randval;
  }

  function generate_hash() {
    // Make an eight character base 36 garbage fds3ys79 / also check that we haven't used this ID already
    $randval = $this->get_hash_str();
    while (strlen($randval) < 8 || $this->set_hash($randval,false)) {
      $randval.= $this->get_hash_str();
      $randval = substr($randval, -8);
    }
    return $randval;
  }

  function select_token_by_entity_and_action($entity,$entityID,$action) {
    $q = prepare("SELECT token.*, tokenAction.*
                    FROM token 
               LEFT JOIN tokenAction ON token.tokenActionID = tokenAction.tokenActionID 
                   WHERE tokenEntity = '%s' 
                     AND tokenEntityID = %d
                     AND tokenAction.tokenActionMethod = '%s'
                ",$entity,$entityID,$action);
    $db = new db_alloc();
    $db->query($q);
    if ($db->next_record()) {
      $this->set_id($db->f("tokenID"));
      $this->select();
      return true;
    }
  }

  function get_list_filter($filter=array()) {
    $filter["tokenEntity"]   and $sql[] = sprintf_implode("token.tokenEntity = '%s'", $filter["tokenEntity"]);
    $filter["tokenEntityID"] and $sql[] = sprintf_implode("token.tokenEntityID = %d", $filter["tokenEntityID"]);
    $filter["tokenHash"]     and $sql[] = sprintf_implode("token.tokenHash = '%s'", $filter["tokenHash"]);
    return $sql;
  }
  
  function get_list($_FORM) {
    $filter = token::get_list_filter($_FORM);
    
    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT * FROM token ".$filter; 
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->next_record()) {
      $rows[$row["tokenID"]] = $row;
    }
    return (array)$rows;
  }


}



?>
