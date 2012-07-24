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


class vcs {

  public $name;
  public $repoprefix;
  public $repodir;
  public $commit;
  public $log = " log ";
  public $metadir;
  public $add_everything;
  public $cat;

  function __construct($repo) {
    if (!$repo) {
      $this->error("vcs::__construct: No repo specified: ".$repo);
    }
    if (!is_dir($repo)) {
      $this->error("vcs::__construct: The repo directory does not exist: ".$repo);
    }
    if (!preg_match("/\/$/",$repo)) {
      $repo.= "/"; // make sure repo ends in a slash
    }
    if (!is_dir($repo.$this->metadir)) {
      $this->error("vcs::__construct: The vcs metadata directory does not exist: ".$repo.$this->metadir);
    }

    if (is_dir($repo) && !is_dir($repo.$this->metadir)) {
      $this->msg("vcs::__construct: No metadir found. Initializing repository.");
      $this->init();
    }
  }
  function get() {
    // Check if we're using a VCS
    $class = "vcs_".config::get_config_item("wikiVCS");
    if (class_exists($class)) {
      $vcs = new $class(wiki_module::get_wiki_path());
    }
    return $vcs;
  }
  function error($msg="") {
    //echo "\n<br>vcs->error: ".$msg;
  }
  function msg($msg="") {
    //echo "\n<br>vcs->msg: ".$msg;
  }
  function juggle_command_order($name, $command, $repo) {
    return $name." ".$command." ".$repo;
  }
  function run($command) {
    if ($command) {
      // 2>&1 nope
      $str = $this->juggle_command_order($this->name, $command, $this->repoprefix);
      $this->debug and print "<br>vcs->run: ".page::htmlentities($str);
      list($output, $result) = $this->exec($str);
      if ($result != 0) {
        //$error = $str."<br>".implode("<br>", $output)."<br>".$result;
        //$this->error("vcs::run:error ".$error);
        $output and $this->error("vcs::run:error: ".implode("<br>",$output));
        return $output;
      }
      $output and $this->msg("vcs::run:msg: ".implode("<br>",$output));
      return $output;
    }
  }
  function exec($str) {
    $this->msg("vcs::exec:command: ".$str);
    $oldUMask = umask(0002);
    exec($str,$output,$result);
    umask($oldUMask);
    $this->msg("vcs::exec:result: ".sprintf("%d ",$result).implode("<br>",$output));
    return array($output,$result);
  }
  function init() {
    $this->run("init");
    $this->run($this->add_everything);
    $this->commit("", "Initial import.");
  }
  function commit($file,$message=false) {
    $message or $message = "Modified file.";
    if (!$this->file_in_vcs($file)) {
      $this->add($file);
    }
    $this->run($this->commit." ".escapeshellarg($message)." ".escapeshellarg($file));
  }
  function log($file) {
    return $this->run($this->log." ".escapeshellarg($file));
  }
  function cat($file, $revision) {
    $lines = $this->run(sprintf($this->cat, escapeshellarg($file), escapeshellarg($revision)
                                ,escapeshellarg(str_replace(wiki_module::get_wiki_path(),"",$file))));
    $lines or $lines = array();
    return implode("\n",$lines);
  }
  function diff() {
  }
  function add($file) {
    $this->run("add ".escapeshellarg($file));
  }
  function rm($src, $message) {
    $this->run("rm ".escapeshellarg($src));
    $this->run($this->commit." ".escapeshellarg($message)." ".escapeshellarg($src));
  }
  function mv($src, $dst, $message) {
    $this->run("mv ".escapeshellarg($src)." ".escapeshellarg($dst));
    $this->run($this->commit." ".escapeshellarg($message)." ".escapeshellarg($src)." ".escapeshellarg($dst));
  }
  function format_log($logs=array()) {
    /*
      We're expecting each log entry to look like this:
      Hash: r2432432
      Author: Alex Lance
      Date: 43242432
      Msg: This is the commit message
    */

    $logs or $logs = array();
    $rtn or $rtn = array();
    foreach($logs as $line) {
      if (preg_match("/^Hash: (\w+)/",$line,$matches)) {
        $id = $matches[1];
      } else if (preg_match("/^Author: (.*$)/",$line,$matches)) {
        $rtn[$id]["author"] = page::htmlentities(trim($matches[1]));
      } else if (preg_match("/^Date: (.*$)/",$line,$matches)) {
        $rtn[$id]["date"] = date("Y-m-d H:i:s",page::htmlentities(trim($matches[1])));
      } else if (preg_match("/^Msg: (.+$)/",$line,$matches)) {
        $rtn[$id]["msg"] = page::htmlentities(trim($matches[1]));
      }
    }
    return $rtn;
  }



  function file_in_vcs() {}

}


?>
