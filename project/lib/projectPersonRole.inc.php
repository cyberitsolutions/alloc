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


class projectPersonRole extends db_entity
{
  var $data_table = "projectPersonRole";
  var $display_field_name = "projectID";

  function projectPersonRole() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("projectPersonRoleID");
    $this->data_fields = array("projectPersonRoleName"=>new db_field("projectPersonRoleName")
                               , "projectPersonRoleHandle"=>new db_field("projectPersonRoleHandle")
                               , "projectPersonRoleSortKey"=>new db_field("projectPersonRoleSortKey")
      );
  }



}



?>
