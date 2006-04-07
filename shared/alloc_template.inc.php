<?php


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
  echo "<!-- start $filename -->\n";
  $template = get_template($filename, is_object($function_object));
   echo htmlspecialchars($template); // GOOD PLACE TO DEBUG
die();
  eval($template);
  echo "<!-- end $filename -->\n";
} 





?>
