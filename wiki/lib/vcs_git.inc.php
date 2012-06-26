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


class vcs_git extends vcs {

  function __construct($repo) {
    $current_user = &singleton("current_user");
    //$this->debug = true;
    $this->name = "git ";
    $this->repodir = $repo;
    $this->repoprefix = " --git-dir '".$repo.".git' --work-tree '".$repo."' ";
    $this->commit = " commit --author '".$current_user->get_name()." <".$current_user->get_value("emailAddress").">' -m ";
    $this->metadir = ".git";
    $this->add_everything = " add ".$repo."/. "; 
    $this->log = " log --pretty=format:'Hash: %H%nAuthor: %an%nDate: %ct%nMsg: %s' -M -C --follow ";
    $this->cat = ' show %2$s:%3$s ';
    parent::__construct($repo);
  }

  function file_in_vcs($file) {
    $output = $this->run("log ".$file);
    if (count($output) > 0 && !preg_match("/^fatal:/",current($output))) {
      return true;
    }
  }

  function juggle_command_order($name, $command, $repo) {
    return $name." ".$repo." ".$command;
  }

}


?>
