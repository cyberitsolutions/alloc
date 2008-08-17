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



// Read a template and return the mixed php/html, ready for processing
function get_template($filename) {

  $template = implode("", (@file($filename)));
  if ((!$template) or(empty($template))) {
    echo "get_template() failure: [$filename]";
  }

  // This allows us to use curly braces in our templates for javascript and CSS rules
  // as long as we escape the curly brace using a backslash. The TPL_*_BRACE keyword
  // gets replaces by an actual curly brace later on in this template function.
  $pattern = '\}';
  $replace = 'TPL_END_BRACE';
  $template = str_replace($pattern,$replace,$template);
  $pattern = '\{';
  $replace = 'TPL_START_BRACE';
  $template = str_replace($pattern,$replace,$template);

  // Replace {$arr.something} with echo $arr["something"]; 
  $pattern = '/{\$([\w|\d|_]+)\.([^}]+)}/i';
  $replace = '<?php echo $${1}["${2}"]; ?>';
  $template = preg_replace($pattern,$replace,$template);

  // Replace {$var_name} with echo $TPL["var_name"]; 
  $pattern = '/{\$([\w|\d|_]+)}/i';
  $replace = '<?php echo strlen($TPL["${1}"]) ? $TPL["${1}"] : $${1}; ?>';
  $template = preg_replace($pattern,$replace,$template);


  // Replace {if hey}    with if (hey) { 
  // Replace {while hey} with while (hey) { 
  // Replace {foreach hey} with foreach (hey) { 
  $pattern = '/{(if|while|foreach){1} ([^}]*)}/i';
  $replace = '<?php ${1} (${2}) TPL_START_BRACE ?>';
  $template = preg_replace($pattern,$replace,$template);

  
  // Replace {else with {TPL_END_BRACE else
  $pattern = '{else';
  $replace = '{TPL_END_BRACE else';
  $template = str_replace($pattern,$replace,$template);

  // Replace {TPL_END_BRACE else} with {TPL_END_BRACE else TPL_START_BRACE
  $pattern = '{TPL_END_BRACE else}';
  $replace = '{TPL_END_BRACE else TPL_START_BRACE}';
  $template = str_replace($pattern,$replace,$template);

  // Replace {TPL_END_BRACE else if hey} with {TPL_END_BRACE else if (hey) TPL_START_BRACE}
  $pattern = '/{TPL_END_BRACE else\s?if\s?([^}]+)}/i';
  $replace = '{TPL_END_BRACE else if (${1}) TPL_START_BRACE}';
  $template = preg_replace($pattern,$replace,$template);

  
  $sr = array("{/}"             => "<?php TPL_END_BRACE ?>"
             ,"{"               => "<?php "
             ,"}"               => " ?>"
             ,"TPL_END_BRACE"   => "}"
             ,"TPL_START_BRACE" => "{"
             ,".php&"           => ".php?"
            );

  foreach ($sr as $s => $r) {
    $searches[] = $s;
    $replaces[] = $r;
  }
  $template = str_replace($searches,$replaces,$template);


  return "?>$template<?php ";
}


// This is the publically callable function, used to include template files
function include_template($filename) {
  global $TPL, $current_user;
  $TPL["current_user"] = $current_user;
  #echo "<!-- Start $filename -->\n";
  $template = get_template($filename);
  #echo "<pre>".htmlspecialchars($template)."</pre>"; 

  // Make all variables available via $var
  is_array($TPL) && extract($TPL, EXTR_SKIP);

  eval($template);
  #echo "<!-- End $filename -->\n";
} 





?>
