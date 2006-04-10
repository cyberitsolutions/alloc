<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */



// Read a template and return the mixed php/html, ready for processing
function get_template($filename, $use_function_object = false) {

  $template = implode("", (@file($filename)));
  if ((!$template) or(empty($template))) {
    echo "get_template() failure: [$filename]";
  }

  // Replace {var_name} with echo $TPL["var_name"]
  $pattern = '/{([\w|\d|_]+)}/i';
  $replace = '<?php echo stripslashes($TPL["${1}"]); ?>';
  $template = preg_replace($pattern,$replace,$template);

  // Replace {:function_name param} with function("param");
  if ($use_function_object) {
    $pattern = '/{:(\w+)\s?([^}]*)}/i';
    $replace = '<?php $function_object->${1}("${2}"); ?>';
    $template = preg_replace($pattern,$replace,$template);

  } else {
    $pattern = '/{:(\w+)\s?([^}]*)}/i';
    $replace = '<?php ${1}("${2}"); ?>';
    $template = preg_replace($pattern,$replace,$template);
  }

  $pattern = '/{optional:([\w]+) ?([^}]*)}/i';
  $replace = '<?php if (check_optional_${1}("${3}")) { ?>';
  $template = preg_replace($pattern,$replace,$template);

  $pattern = '/{\/optional}/i';
  $replace = '<?php } ?>';
  $template = preg_replace($pattern,$replace,$template);

  $pattern = '.php&';
  $replace = '.php?';
  $template = str_replace($pattern,$replace,$template);

  return "?>$template<?php ";
}


// This is the publically callable function, used to include template files
function include_template($filename, $function_object = "") {
  global $TPL;
  echo "\n<!-- start $filename -->\n";
  $template = get_template($filename, is_object($function_object));
#   echo htmlspecialchars($template); // GOOD PLACE TO DEBUG
#die();
  eval($template);
  echo "\n<!-- end $filename -->\n";
} 





?>
