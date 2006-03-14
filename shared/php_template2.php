<?php
/* PHPTemplate 1.0 Template functions for PHP scripts Template files containing {VAR_NAME} have values replaced using values from an associative array These routines aim to be faster to program with and run than any other templating method I have found elsewhere They contain basically NO ERROR
     CHECKING in order to be as fast as possible (c) Clancy Malcolm 2000 clancy@cyber.com.au Inspired by FastTemplates by CDI, cdi@thewebmasters.net You are free to copy and use this file as is provided this header comment remains in the file in its entirity. You are free to modify this file
     provided that the modifications can be copied, used and modified under these same terms and the original product and copyright is clearly acknowledged at the beginning of the file.  This product is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  */
# Read a template
# returns the template parsed, ready for processing
# $filename - the name of the file containing the template
  function get_template($filename, $for_saving = false, $use_function_object = false) {
  global $debug;
  $template = implode("", (@file($filename)));
  if ((!$template) or(empty($template))) {
    echo "get_template() failure: [$filename]";
  }
  // Replace {var_name} with echo $TPL["var_name"]
  $template = ereg_replace("\\{([A-Za-z0-9_]+)\\}", "<?php echo stripslashes(\$TPL[\"\\1\"]); ?>", $template);

  // Replace {:function_name param} with function("param");
  if ($use_function_object) {
    $template = ereg_replace("\\{:([A-Za-z0-9_]+) ?([^}]*)\\}", "<?php \$function_object->\\1(\"\\2\"); ?>", $template);
  } else {
    $template = ereg_replace("\\{:([A-Za-z0-9_]+) ?([^}]*)\\}", "<?php \\1(\"\\2\"); ?>", $template);
  }

  // Replace {optional:block_name param} with if (check_optional_block_name("param")) {
  $template = ereg_replace("\\{optional:([A-Za-z0-9_]+)( )?([^}]*)\\}", "<?php if (check_optional_\\1(\"\\3\")) { ?>", $template);

  // {/optional} ==> }
  $template = ereg_replace("\\{/optional\\}", "<?php } ?>", $template);

  // Replace {if:var} with if ($var) {
  $template = ereg_replace("\\{if:([A-Za-z0-9_]+)( )?([^}]*)\\}", "<?php if (\$\\1) { ?>", $template);

  // {/if} ==> }
  $template = ereg_replace("\\{/if\\}", "<?php } ?>", $template);

  // Replace {repeated:block_name param} with while (check_repeated_block_name("param")) {
  $template = ereg_replace("\\{repeated:([A-Za-z0-9_]+)( )?([^}]*)\\}", "<?php while (check_repeated_\\1(\"\\3\")) { ?>", $template);

  // {/repeated} ==> }
  $template = ereg_replace("\\{/repeated\\}", "<?php } ?>", $template);

  // Comment using /* ...*/ anything between {ignore:} and {/ignore}
  $template = ereg_replace("\\{ignore:\\}", "<?php /*", $template);
  $template = ereg_replace("\\{/ignore\\}", "*/ ?>", $template);

  // Replace all '*.html' and "*.html" URL's (not containing the ':' character) with references to their screens using main.php instead
  if (REWRITE_TEMPLATE_URLS) {
    $template = ereg_replace("(['\"])([^:\"']+).html[?]?([a-zA-Z0-9_&%=]*)(['\"])", "\\1<?php echo screen_url(\"\\2\", \"\\3\"); ?>\\4", $template);

    // Replace ../image directories with images/
    $template = str_replace("../images", "images", $template);
  }
  if (!$for_saving) {
    $template = "?>$template<?php ";
  }
  if ($debug) {
    echo "<hr><b>Debug: PHP version of $filename is as follows...</b><br>";
    echo "<pre>".htmlspecialchars($template)."</pre>";
    echo "<hr>";
  }
  return $template;
}


# Output a template to the user
# No return value
# $template - the template as returned by get_template
# $tpl_values - An array in the form "template_var_name"=> "template_var_value" used to provide values to the template
function print_template($template, $function_object = "") {
  if (is_array($tpl_values)) {
    $TPL = tpl_values;
  } else {
    global $TPL;
  }
  // echo htmlspecialchars($template); // GOOD PLACE TO DEBUG
  eval($template);
} function include_template($filename, $function_object = "") {
  echo "<!-- start $filename -->\n";
  $template = get_template($filename, false, is_object($function_object));
  print_template($template, $function_object);
  echo "<!-- end $filename -->\n";
} function get_template_string_code($filename) {
  global $debug;
  $template = implode("", (@file($filename)));
  if ((!$template) or(empty($template))) {
    echo "get_template() failure: [$filename]";
  }
  $template = "\$html = \"".addslashes($template)."\";";

  // Replace {var_name} with echo $TPL["var_name"]
  $template = ereg_replace("\\{([A-Za-z0-9_]+)\\}", "\" . \$TPL[\"\\1\"] . \"", $template);

  // Replace {:function_name param} with function("param");
  $template = ereg_replace("\\{:(show_)?([A-Za-z0-9_]+) ?([^}]*)\\}", "\" . get_\\2(\"\\3\") . \"", $template);

  // Replace {optional:block_name param} with if (check_optional_block_name("param")) {
  $template = ereg_replace("\\{optional:([A-Za-z0-9_]+)( )?([^}]*)\\}", "\"; if (check_optional_\\1(\"\\3\")) {\n \$html .= \"", $template);

  // {/optional} ==> }
  $template = ereg_replace("\\{/optional\\}", "\"; }\n \$html .= \"", $template);

  // Replace {repeated:block_name param} with while (check_repeated_block_name("param")) {
  $template = ereg_replace("\\{repeated:([A-Za-z0-9_]+)( )?([^}]*)\\}", "\"; while (check_repeated_\\1(\"\\3\")) {\n \$html .= \"", $template);

  // {/repeated} ==> }
  $template = ereg_replace("\\{/repeated\\}", "\"; }\n \$html .= \"", $template);

  // Comment using /* ...*/ anything between {ignore:} and {/ignore}
  $template = ereg_replace("\\{ignore:\\}", "\"; /*", $template);
  $template = ereg_replace("\\{/ignore\\}", "*/ $html .= ", $template);

  // Replace all '*.html' and "*.html" URL's (not containing the ':' character) with references to their screens using main.php instead
  if (REWRITE_TEMPLATE_URLS) {
    $template = ereg_replace("(['\"])([^:\"']+).html[?]?([a-zA-Z0-9_&%=]*)(\\['\"])", "\\1\" . screen_url(\"\\2\", \"\\3\") . \"\\4", $template);

    // Replace ../image directories with images/
    $template = str_replace("../images", "images", $template);
  }
  if ($debug) {
    echo "<hr><b>Debug: PHP version of $filename is as follows...</b><br>";
    echo "<pre>".htmlspecialchars($template)."</pre>";
    echo "<hr>";
  }
  return $template;
}

function get_template_string($filename) {
  global $TPL;                  // Used by the eval'd code
  $template = get_template_string_code($filename);
  eval($template);
  return $html;
}




?>
