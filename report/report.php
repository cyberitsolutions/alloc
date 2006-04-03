<?php
include("alloc.inc");

if (!has_report_perm()) {
  die("you don't have permission to generate reports.");
}



global $modules, $TPL, $mod, $do_step_2, $do_step_3;
$TPL["mod"] = $mod;
$TPL["do_step_2"] = $do_step_2;
$TPL["do_step_3"] = $do_step_3;


function check_optional_step_1() {
  global $TPL, $mod;
  $modules["transaction"] = "Transactions";
  $modules["invoice"] = "Invoices";
  $modules["project"] = "Projects";
  $modules["time"] = "Time Sheets";
  $modules["client"] = "Clients";
  $modules["item"] = "Items";
  $modules["person"] = "Users";
  $modules["announcement"] = "Announcements";

  foreach($modules as $k=>$v) {
    $TPL["module_options"].= get_option($v, $k, $mod == $k);
  }
  return true;
}

// END STEP ONE


function check_optional_step_2() {
  global $do_step_2, $mod, $TPL, $do_step_3, $table_fields, $table_name, $table_like, $query, $ignored_fields;
  global $table_num_op_1, $table_num_op_2, $table_groupby;

  if (!is_array($fields)) {
    $fields = array();
  }


  if ($do_step_2) {
    $ignored_fields = array();
    $table_fields = array();
    $db_tables = array();


    if ($mod == "client") {
      $db_tables[] = "client";
      $db_tables[] = "clientContact";
      $db_tables[] = "comment";
      $query["join"] = " LEFT JOIN clientContact ON client.clientID = clientContact.clientID";
      $query["join"].= " LEFT JOIN comment ON (client.clientID = comment.commentLinkID AND commentType = 'client')";
    }

    if ($mod == "project") {
      $db_tables[] = "project";
      $db_tables[] = "projectCommissionPerson";
      $db_tables[] = "projectModificationNote";
      $db_tables[] = "projectPerson";
      $query["join"] = " LEFT JOIN projectCommissionPerson ON project.projectID = projectCommissionPerson.projectID";
      $query["join"].= " LEFT JOIN projectModificationNote ON project.projectID = projectModificationNote.projectID";
      $query["join"].= " LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID";
    }


    if ($mod == "time") {
      $db_tables[] = "timeSheet";
      $db_tables[] = "timeSheetItem";
      $query["join"] = " LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID";
    }

    if ($mod == "transaction") {
      $db_tables[] = "tf";
      $db_tables[] = "transaction";
      $db_tables[] = "transactionRepeat";
      $db_tables[] = "expenseForm";
      $query["join"].= " LEFT JOIN transaction ON transaction.tfID = tf.tfID";
      $query["join"].= " LEFT JOIN transactionRepeat ON transaction.tfID = transactionRepeat.tfID";
      $query["join"].= " LEFT JOIN expenseForm ON expenseForm.expenseformID = transaction.expenseformID";
    }

    if ($mod == "invoice") {
      $db_tables[] = "invoice";
      $db_tables[] = "invoiceItem";
      $query["join"].= " LEFT JOIN invoiceItem on invoice.invoiceID = invoiceItem.invoiceID";
    }

    if ($mod == "item") {
      $db_tables[] = "item";
    }

    if ($mod == "person") {
      $db_tables[] = "person";
    }

    if ($mod == "announcement") {
      $db_tables[] = "announcement";
    }


    /* 
       this is how to not include particular fields $ignored_fields[] = "timeSheetItem.timeSheetItemID"; */

    $query["start"] = " SELECT ";
    $query["where"] = " WHERE 1=1 ";
    $query["from"] = " FROM ".$db_tables[0];



    foreach($db_tables as $table) {
      if (class_exists($table)) {
        $class = new $table;
        $TPL["table_fields"].= "<tr><td colspan=\"6\">&nbsp;</td></tr>";
        $TPL["table_fields"].= "<tr><td colspan=\"6\"><b>".strtoupper($table)."</b></td></tr>";
        if (is_object($class) && $class->key_field->label == ($table."ID")) {
          if (count($db_tables) > 1) {
            $groupby_str = $table.".".$table."ID";
            $groupby = "<input type=\"radio\" name=\"table_groupby\" value=\"";
            $groupby.= $groupby_str."\"";
            $groupby.= ($table_groupby == $groupby_str ? " checked" : "").">";
            $groupby = "<b>Group by this table</b> ".$groupby;
          } else {
            $groupby = "&nbsp;";
          }
        }
        $TPL["table_fields"].= "<tr><td colspan=\"6\">".$groupby."</td></tr><tr>";
        $TPL["table_fields"].= "<td>&nbsp;</td>";
        $TPL["table_fields"].= "<td><b>Like (john smit%)</b></td><td><b>Numerical (>=5)</b></td>";
        $TPL["table_fields"].= "<td>&nbsp;</td>";
        $TPL["table_fields"].= "<td><b>Like (john smit%)</b></td><td><b>Numerical (>=5)</b></td>";
        $TPL["table_fields"].= "</tr>";
        unset($i);
        foreach($class->data_fields as $name=>$v) {
          $str = $table.".".$name;
          if ($name != $class->key_field && !in_array($str, $ignored_fields)) {
            $table_fields[] = $str;
            $TPL["table_fields"].= "<td valign=\"middle\">";
            $TPL["table_fields"].= "\n<input type=\"checkbox\" name=\"table_name[".$str."]\" value=\"";
            $TPL["table_fields"].= $str."\"".($table_name[$str] == $str ? " checked" : "").">";
            $TPL["table_fields"].= $name;
            $TPL["table_fields"].= "</td><td valign=\"middle\">";
            $TPL["table_fields"].= "\n<input type=\"text\" name=\"table_like[".$str."]\"";
            $TPL["table_fields"].= "value=\"".$table_like[$str]."\"size=\"15\">\n";
            $TPL["table_fields"].= "</td><td valign=\"middle\">";
            $TPL["table_fields"].= "<input type=\"text\" name=\"table_num_op_1[".$str."]\" value=\"";
            $TPL["table_fields"].= $table_num_op_1[$str]."\" size=\"8\"> and ";
            $TPL["table_fields"].= "<input type=\"text\" name=\"table_num_op_2[".$str."]\" value=\"";
            $TPL["table_fields"].= $table_num_op_2[$str]."\" size=\"8\">";
            $TPL["table_fields"].= "</td>";
            if (isset($i)) {
              $TPL["table_fields"].= "</tr><tr>";
              unset($i);
            } else {
              $i = "set";
            }
          }
        }
      } else {
        die("class ".$table." does not exist.. ");
      }
    }
    $TPL["table_fields"].= "</tr>";

    global $field_separator, $field_quotes, $generate_file;

    if ($field_quotes == "single") {
      $s_q_sel = " selected";
    } else if ($field_quotes == "double") {
      $d_q_sel = " selected";
    }

    if ($generate_file) {
      $g_f_sel = " checked";
    }

    $TPL["dump_options"].= "Quotes around fields: ";
    $TPL["dump_options"].= "<select name=\"field_quotes\">";
    $TPL["dump_options"].= "<option value=\"\">None";
    $TPL["dump_options"].= "<option value=\"single\"".$s_q_sel.">Single";
    $TPL["dump_options"].= "<option value=\"double\"".$d_q_sel.">Double";
    $TPL["dump_options"].= "</select><br>";
    $TPL["dump_options"].= "Generate File: ";
    $TPL["dump_options"].= "<input type=\"checkbox\" name=\"generate_file\"".$g_f_sel."> ";
    $TPL["dump_options"].= " with field separator ";
    $TPL["dump_options"].= "<input type=\"text\" name=\"field_separator\" size=\"5\" value=\"";
    $TPL["dump_options"].= $field_separator."\"> (type 'tab' for tab).";
    return true;
  }
}


// END STEP TWO

function check_optional_step_3() {
  global $do_step_3, $table_fields, $table_name, $table_like, $query, $ignored_fields;
  global $table_num_op_1, $table_num_op_2, $TPL, $table_groupby;


  if ($do_step_3) {

    if (!is_array($table_fields)) {
      die("did not get table_fields array bugger");
      $table_fields = array();
    }


    while (list(, $v) = each($table_fields)) {

      if ($table_name[$v] != "") {
        $query["select"].= $commar.$table_name[$v];
        $commar = ",";          // no commar the first time

        if ($table_like[$v] != "") {
          $query["where"].= " AND ".$table_name[$v]." LIKE '".$table_like[$v]."'";
        }

        if ($table_num_op_1[$v] != "") {
          $query["where"].= " AND ".$table_name[$v]." ".$table_num_op_1[$v];
        }

        if ($table_num_op_2[$v] != "") {
          $query["where"].= " AND ".$table_name[$v]." ".$table_num_op_2[$v];
        }
      }
    }

    if ($table_groupby != "") {
      if (!isset($query["group"])) {
        $query["group"] = " GROUP BY ".$table_groupby;
      } else {
        $query["group"].= ",".$table_groupby;
      }
    }



    $final_query = $query["start"].$query["select"].$query["from"].$query["join"].$query["where"].$query["group"];

    if ($query["select"] == "") {
      exit;
    }
    $db = new db_alloc;
    $db->query($final_query);

    $fields = explode(",", $query["select"]);
    $TPL["result_row"] = "<tr>";
    foreach($fields as $field) {
      $TPL["result_row"].= "<th>".$field."</th>";
    }
    $TPL["result_row"].= "</tr>";

    global $field_separator, $field_quotes, $generate_file;
    if (!$generate_file) {
      $start_row_separator = "<tr>";
      $end_row_separator = "</tr>\n";
      $start_field_separator = "<td>&nbsp;";
      $end_field_separator = "</td>";
    } else {
      unset($TPL["result_row"]);
      $start_row_separator = "";
      $end_row_separator = "\n";
      $start_field_separator = "";
      if ($field_separator == 'tab') {
        $end_field_separator = chr(9);
      } else {
        $end_field_separator = $field_separator;
      }
    }

    if ($field_quotes == "single") {
      $quotes = "'";
    }
    if ($field_quotes == "double") {
      $quotes = "\"";
    }


    while ($db->next_record()) {
      $TPL["result_row"].= $start_row_separator;
      foreach($fields as $k=>$field) {
        $field = end(explode(".", $field));
        if (eregi("ModifiedUser", $field) || eregi("personID", $field)) {
          $person = new person;
          $person->set_id($db->f($field));
          $person->select();
          
          $result = $person->get_username();
        } else if (eregi("tfID", $field)) {
          $result = get_tf_name($db->f($field));
        } else {
          $result = $db->f($field);
        }
        $TPL["result_row"].= $start_field_separator;
        $TPL["result_row"].= $quotes.$result.$quotes;
        if (isset($fields[$k + 1]) || !$generate_file) {
          $TPL["result_row"].= $end_field_separator;
        }
        $TPL["counter"]++;
      }
      $TPL["result_row"].= $end_row_separator;
    }

    global $MOD_DIR;

    if ($generate_file) {
      // write to file
      $filename = substr(mktime(), -2, 2).".txt";
      $path_and_file = $MOD_DIR."/report/files/".$filename;
      $fp = fopen($path_and_file, "w+");
      fputs($fp, $TPL["result_row"]);
      fclose($fp);
      $file = $SCRIPT_PATH."files/".$filename;
      // exit;
      // header("Location: www.yahoo.com");
      // header("Content-type: application/octet-stream");
      // header("Content-Disposition: attachment; filename=" . $file);
      // fpassthru($file);
      $TPL["filelink"] = "<a href=\"".$file."\">Download File</a>";
      unset($TPL["result_row"]);
    }


    return true;
  }
}


// END STEP THREE



include_template("templates/reportM.tpl");











?>
