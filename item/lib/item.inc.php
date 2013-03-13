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

class item extends db_entity {
  public $classname = "item";
  public $data_table = "item";
  public $display_field_name = "itemName";
  public $key_field = "itemID";
  public $data_fields = array("itemModifiedUser"
                             ,"itemName"
                             ,"itemAuthor"
                             ,"itemNotes"
                             ,"itemModifiedTime"
                             ,"itemType"
			                       ,"personID"
                             );

  function update_search_index_doc(&$index) {
    $p =& get_cached_table("person");
    $personID = $this->get_value("personID");
    $person_field = $personID." ".$p[$personID]["username"]." ".$p[$personID]["name"];
    $itemModifiedUser = $this->get_value("itemModifiedUser");
    $itemModifiedUser_field = $itemModifiedUser." ".$p[$itemModifiedUser]["username"]." ".$p[$itemModifiedUser]["name"];

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$this->get_value("itemName"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$this->get_value("itemNotes"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('type'    ,$this->get_value("itemType"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('author'  ,$this->get_value("itemAuthor"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$person_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('modifier',$itemModifiedUser_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateModified',str_replace("-","",$this->get_value("itemModifiedTime")),"utf-8"));
    $index->addDocument($doc);
  }
}



?>
