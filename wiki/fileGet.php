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

define("NO_REDIRECT",1);
require_once("../alloc.php");

usleep(50000);

list($file, $rev) = explode("|",$_GET["file"]);
$file = realpath(get_wiki_path().urldecode($file));

$wikiMarkup = config::get_config_item("wikiMarkup");

if (path_under_path(dirname($file), get_wiki_path()) && is_file($file) && is_readable($file)) {

  $filelabel = str_replace(get_wiki_path(),"",$file);

  // Get the regular revision ...
  $disk_file = file_get_contents($file) or $disk_file = "";

  $vcs = vcs::get();
  //$vcs->debug = true;

  // Get a particular revision
  if ($vcs) { 
    $vcs_file = $vcs->cat(urldecode($file), urldecode($rev));
  }

  // for some reason trailing slashes on a line appear to not get saved by
  // particular vcs's. So when we compare the two files (the one on disk and
  // the one in version control, we need to nuke trailing spaces, from every
  // line ... sigh).

  function nuke_trailing_spaces_from_all_lines($str) {
    $lines or $lines = array();
    $str = str_replace("\r\n","\n",$str);
    $bits = explode("\n",$str);
    foreach($bits as $line) {
      $lines[] = rtrim($line);
    }
    return rtrim(implode("\n",$lines));
  }

  if ($vcs && nuke_trailing_spaces_from_all_lines($disk_file) != nuke_trailing_spaces_from_all_lines($vcs_file)) {

    if (!$vcs_file) {
      $msg = "<div class='message warn' style='margin-top:0px; margin-bottom:10px; padding:10px;'>
                Warning: This file may not be under version control.
              </div>";
    } else {

      $msg = "<div class='message warn' style='margin-top:0px; margin-bottom:10px; padding:10px;'>
                Warning: This file may not be the latest version.
              </div>";

    }
  
    //echo "<br>".md5(nuke_trailing_spaces_from_all_lines($disk_file));
    //$h = fopen("/tmp/disk_file","w+");
    //fputs($h,$disk_file);
    //fclose($h);
    //$h = fopen("/tmp/vcs_file","w+");
    //fputs($h,$vcs_file);
    //fclose($h);
    //echo "<br><br>".md5(nuke_trailing_spaces_from_all_lines($vcs_file));
    //#echo page::htmlentities($disk_file);
    //#echo page::htmlentities($vcs_file);
  }

  if ($rev && $vcs_file) {
    $str = $vcs_file;
  } else {
    $str = $disk_file;
  }
  //$str = page::htmlentities($str);
  // the class=>processed prevents the default grippie being added to this textarea
  $textarea = page::textarea('wikitext',$str,array("height"=>"large","width"=>"100%","class"=>"processed")); 
  $str = $wikiMarkup($str); 

  //$str = rtrim($str);
  //echo "<pre>".page::htmlentities($str)."</pre>"; // will echo the html

  is_writable($file) and $edit_button = "<input type=\"button\" value=\"Edit File\" onClick=\"$('.view').hide();$('.edit').show();\">";

  // Check if we're using a VCS
  $class = "vcs_".config::get_config_item("wikiVCS");
  if (class_exists($class)) {
    $commit_msg = '<br><input type="text" name="commit_msg" id="commit_msg" value="" style="margin-top:10px; width:100%;">';
    $commit_msg.= '<script>preload_field("#commit_msg", "Enter a brief description of your changes...");</script>';
  }

  $commit_msg.= '<script>mySettings.previewParserPath="'.$TPL["url_alloc_filePreview"].'"; $("#wikitext").markItUp(mySettings);</script>';


  //<h6 style="margin-top:0px;text-transform:none; color:#333333; font-weight:bold;">${filelabel}</h6>
  //<h6 style="margin-top:0px;">Document</h6>
  $rev = urlencode($rev);

  $rtn =<<<EOD
  <div class="view">
    {$msg}
    <div class="wikidoc">
      <div style="float:right; display:inline; width:30px; margin-top:10px; right:-10px; position:relative;" class="noprint">
        <a target="_blank" href="{$url_alloc_wiki}?media=print#{$filelabel}|{$rev}"><img class="noprint" border="0" src="{$TPL["url_alloc_images"]}printer.png"></a>
      </div>
      ${str}
    </div>
    <h5 class="jftBot noprint">${edit_button}</h5>
  </div>
EOD;

  if ($edit_button) {
    $rtn.=<<<EOD2
    <div class="edit noprint">
      <!-- 
      <h5 class="jftTop">
        <input name="origName" type="hidden" value="${filelabel}">
        <input name="editName" type="text" style="width:100%;" value="${filelabel}"> 
      </h5>
      -->
      <form action="{$TPL["url_alloc_fileSave"]}" method="post">
        ${textarea}
        ${commit_msg}
        <h5 class="jftBot">
          <input type="hidden" name="file" value="${file}">
          <input type="submit" name="save" value="Save">
        </h5>
      </form>
    </div>
EOD2;
  }

  echo $rtn;

}

?>
