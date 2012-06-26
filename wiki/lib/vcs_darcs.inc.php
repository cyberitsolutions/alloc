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


class vcs_darcs extends vcs {

  function __construct($repo) {
    $current_user = &singleton("current_user");
    $this->name = "darcs ";
    $this->repodir = $repo;
    $this->repoprefix = " --repodir=".$repo." ";
    $this->commit = " rec --author=".escapeshellarg($current_user->get_name()." <".$current_user->get_value("emailAddress").">")." --all -m ";
    $this->log = " changes --xml-output ";
    $this->metadir = "_darcs";
    $this->add_everything = " add -r . ";
    $this->cat = ' show contents %1$s --match %2$s ';
    parent::__construct($repo);
  }

  function file_in_vcs($file) {
    $output = $this->run("changes --count ".$file);
    if (end($output) > 0) {
      return true;
    }
  }

  function format_log($msg="") {
    $rtn = array();
    $msg = implode(" ",$msg);
    if ($msg) {
      $xml = new SimpleXMLElement($msg);
      if (is_object($xml) && is_object($xml->patch)) {
        foreach($xml->patch as $attr) {
          $id = "hash ".(string)$attr["hash"];
          $rtn[$id]["author"] = (string)$attr["author"];
          //$rtn[$id]["date"] = date("Y-m-d H:i:s",(string)$attr["date"]);
          preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",(string)$attr["date"],$m);
          $rtn[$id]["date"] = $m[1]."-".$m[2]."-".$m[3]." ".$m[4].":".$m[5].":".$m[6];
          $rtn[$id]["msg"] = (string)$attr->name;
        }
      }
    }
    return $rtn;
  }


}


?>
