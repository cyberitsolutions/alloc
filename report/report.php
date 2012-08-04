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

require_once("../alloc.php");

if (!has_report_perm()) {
  alloc_error("you don't have permission to generate reports.",true);
}



$TPL["mod"] = $_POST["mod"];
$TPL["do_step_2"] = $_POST["do_step_2"];
$TPL["do_step_3"] = $_POST["do_step_3"];

$modules = array();
$modules["transaction"] = "Transactions";
$modules["invoice"] = "Invoices";
$modules["project"] = "Projects";
$modules["task"] = "Tasks";
$modules["time"] = "Time Sheets";
$modules["client"] = "Clients";
$modules["item"] = "Items";
$modules["person"] = "Users";
$modules["announcement"] = "Announcements";

$TPL["module_options"] = page::select_options($modules,$_POST["mod"]);


if ($_POST["do_step_2"]) {

  if (!is_array($fields)) {
    $fields = array();
  }
  $ignored_fields = array();
  $table_fields = array();
  $db_tables = array();


  if ($_POST["mod"] == "client") {
    $db_tables[] = "client";
    $db_tables[] = "clientContact";
    $db_tables[] = "comment";
    $query["join"] = " LEFT JOIN clientContact ON client.clientID = clientContact.clientID";
    $query["join"].= " LEFT JOIN comment ON (client.clientID = comment.commentLinkID AND commentType = 'client')";
  }

  if ($_POST["mod"] == "project") {
    $db_tables[] = "project";
    $db_tables[] = "projectCommissionPerson";
    $db_tables[] = "projectPerson";
    $query["join"] = " LEFT JOIN projectCommissionPerson ON project.projectID = projectCommissionPerson.projectID";
    $query["join"].= " LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID";
  }

  if ($_POST["mod"] == "task") {
    $db_tables[] = "task";
  }

  if ($_POST["mod"] == "time") {
    $db_tables[] = "timeSheet";
    $db_tables[] = "timeSheetItem";
    $query["join"] = " LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID";
  }

  if ($_POST["mod"] == "transaction") {
    $db_tables[] = "tf";
    $db_tables[] = "transaction";
    $db_tables[] = "transactionRepeat";
    $db_tables[] = "expenseForm";
    $query["join"].= " LEFT JOIN transaction ON transaction.tfID = tf.tfID";
    $query["join"].= " LEFT JOIN transactionRepeat ON transaction.tfID = transactionRepeat.tfID";
    $query["join"].= " LEFT JOIN expenseForm ON expenseForm.expenseformID = transaction.expenseformID";
  }

  if ($_POST["mod"] == "invoice") {
    $db_tables[] = "invoice";
    $db_tables[] = "invoiceItem";
    $query["join"].= " LEFT JOIN invoiceItem on invoice.invoiceID = invoiceItem.invoiceID";
  }

  if ($_POST["mod"] == "item") {
    $db_tables[] = "item";
  }

  if ($_POST["mod"] == "person") {
    $db_tables[] = "person";
  }

  if ($_POST["mod"] == "announcement") {
    $db_tables[] = "announcement";
  }


  /* 
     this is how to not include particular fields $ignored_fields[] = "timeSheetItem.timeSheetItemID"; */

  $query["start"] = " SELECT ";
  $query["where"] = " WHERE 1=1 ";
  $query["from"] = prepare(" FROM %s ",$db_tables[0]);



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
          $groupby.= ($_POST["table_groupby"] == $groupby_str ? " checked" : "").">";
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
      $class->data_fields[$class->key_field->get_name()] = true;
      foreach($class->data_fields as $name=>$v) {
        $str = $table.".".$name;
        if (!in_array($str, $ignored_fields)) {
          $table_fields[] = $str;
          $TPL["table_fields"].= "<td valign=\"middle\">";
          $TPL["table_fields"].= "\n<input type=\"checkbox\" name=\"table_name[".$str."]\" value=\"";
          $TPL["table_fields"].= $str."\"".($_POST["table_name"][$str] == $str ? " checked" : "").">";
          $TPL["table_fields"].= $name;
          $TPL["table_fields"].= "</td><td valign=\"middle\">";
          $TPL["table_fields"].= "\n<input type=\"text\" name=\"table_like[".$str."]\"";
          $TPL["table_fields"].= "value=\"".$_POST["table_like"][$str]."\"size=\"15\">\n";
          $TPL["table_fields"].= "</td><td valign=\"middle\">";
          $TPL["table_fields"].= "<input type=\"text\" name=\"table_num_op_1[".$str."]\" value=\"";
          $TPL["table_fields"].= $_POST["table_num_op_1"][$str]."\" size=\"8\"> and ";
          $TPL["table_fields"].= "<input type=\"text\" name=\"table_num_op_2[".$str."]\" value=\"";
          $TPL["table_fields"].= $_POST["table_num_op_2"][$str]."\" size=\"8\">";
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
      alloc_error("class ".$table." does not exist.. ");
    }
  }
  $TPL["table_fields"].= "</tr>";

  if ($_POST["field_quotes"] == "single") {
    $s_q_sel = " selected";
  } else if ($_POST["field_quotes"] == "double") {
    $d_q_sel = " selected";
  }

  if ($_POST["generate_file"]) {
    $g_f_sel = " checked";
  }
  $_POST["field_separator"] or $_POST["field_separator"] = ",";

  $TPL["dump_options"].= "Generate File: ";
  $TPL["dump_options"].= "<input type=\"checkbox\" name=\"generate_file\"".$g_f_sel."> ";
  $TPL["dump_options"].= " with field separator ";
  $TPL["dump_options"].= "<input type=\"text\" name=\"field_separator\" size=\"5\" value=\"";
  $TPL["dump_options"].= $_POST["field_separator"]."\"> (type 'tab' for tab).";
  $TPL["dump_options"].= "<br>Quotes around fields: ";
  $TPL["dump_options"].= "<select name=\"field_quotes\">";
  $TPL["dump_options"].= "<option value=\"\">None";
  $TPL["dump_options"].= "<option value=\"single\"".$s_q_sel.">Single";
  $TPL["dump_options"].= "<option value=\"double\"".$d_q_sel.">Double";
  $TPL["dump_options"].= "</select><br>";
  $TPL["dump_options"].= '<br><br>
  <button type="submit" name="do_step_3" value="1" class="filter_button">Generate Database Report<i class="icon-cogs"></i></button>
  ';
}


// END STEP TWO

if ($_POST["do_step_3"]) {

  if (!is_array($table_fields)) {
    alloc_error("Did not get table_fields array.");
    $table_fields = array();
  }


  foreach($table_fields as $v) {

    if ($_POST["table_name"][$v] != "") {
      $query["select"].= $commar.db_esc($_POST["table_name"][$v]);
      $commar = ",";          // no commar the first time

      if ($_POST["table_like"][$v] != "") {
        $query["where"].= " AND ".db_esc($_POST["table_name"][$v])." LIKE '".db_esc($_POST["table_like"][$v])."'";
      }

      if ($_POST["table_num_op_1"][$v] != "") {
        $query["where"].= " AND ".db_esc($_POST["table_name"][$v])." ".db_esc($_POST["table_num_op_1"][$v]);
      }

      if ($_POST["table_num_op_2"][$v] != "") {
        $query["where"].= " AND ".db_esc($_POST["table_name"][$v])." ".db_esc($_POST["table_num_op_2"][$v]);
      }
    }
  }

  if ($_POST["table_groupby"] != "") {
    if (!isset($query["group"])) {
      $query["group"] = " GROUP BY ".db_esc($_POST["table_groupby"]);
    } else {
      $query["group"].= ",".db_esc($_POST["table_groupby"]);
    }
  }



  $final_query = $query["start"].$query["select"].$query["from"].$query["join"].$query["where"].$query["group"];

  if ($query["select"]) {
    $db = new db_alloc();
    $db->query($final_query);

    $fields = explode(",", $query["select"]);
    $TPL["result_row"] = "<tr>";
    foreach($fields as $field) {
      $TPL["result_row"].= "<th>".$field."</th>";
    }
    $TPL["result_row"].= "</tr>";

    if (!$_POST["generate_file"]) {
      $start_row_separator = "<tr class='%s'>";
      $end_row_separator = "</tr>\n";
      $start_field_separator = "<td>&nbsp;";
      $end_field_separator = "</td>";
    } else {
      unset($TPL["result_row"]);
      $start_row_separator = "";
      $end_row_separator = "\n";
      $start_field_separator = "";
      if ($_POST["field_separator"] == 'tab') {
        $end_field_separator = chr(9);
      } else {
        $end_field_separator = $_POST["field_separator"];
      }
    }

    if ($_POST["field_quotes"] == "single") {
      $quotes = "'";
    }
    if ($_POST["field_quotes"] == "double") {
      $quotes = "\"";
    }


    while ($db->next_record()) {
      $odd_even = $odd_even == "even" ? "odd" : "even";
      $TPL["result_row"].= sprintf($start_row_separator,$odd_even);
      foreach($fields as $k=>$field) {
        $field = end(explode(".", $field));
        if (stripos("ModifiedUser", $field) !== FALSE || stripos("personID", $field) !== FALSE) {
          $person = new person();
          $person->set_id($db->f($field));
          $person->select();
          
          $result = $person->get_name(array("format"=>"nick"));
        } else if (stripos("tfID", $field) !== FALSE) {
          $result = tf::get_name($db->f($field));
        } else {
          $result = $db->f($field);
        }
        $TPL["result_row"].= $start_field_separator;
        $TPL["result_row"].= $quotes.$result.$quotes;
        if (isset($fields[$k + 1]) || !$_POST["generate_file"]) {
          $TPL["result_row"].= $end_field_separator;
        }
      }
      $TPL["result_row"].= $end_row_separator;
      $counter++;
    }
    $TPL["counter"] = "Number of rows(s): ".$counter;

    if ($_POST["generate_file"]) {
      // write to file
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header('Content-Type: application/octet-stream');
      header('Content-Size: '.strlen($TPL["result_row"]));
      header('Content-Disposition: attachment; filename="csv_'.mktime().'.csv"');
      echo $TPL["result_row"];
      exit;
    }

  } else {
    alloc_error("Please select some Fields using the checkboxes.");
  }

}



$TPL["main_alloc_title"] = "Reports - ".APPLICATION_NAME;

include_template("templates/reportM.tpl");

?>
