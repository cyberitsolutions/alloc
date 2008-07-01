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

require_once("../alloc.php");

$misc_options = array(array("url"=>"reminderList"            ,"text"=>"Reminders"             ,"entity"=>""                   ,"action"=>true)
                     ,array("url"=>"announcementList"        ,"text"=>"Announcements"         ,"entity"=>"announcement"       ,"action"=>PERM_READ_WRITE)
                     ,array("url"=>"permissionList"          ,"text"=>"Security"              ,"entity"=>"permission"         ,"action"=>PERM_READ_WRITE)
                     ,array("url"=>"costtime"                ,"text"=>"Cost & Time Estimater" ,"entity"=>"project"            ,"action"=>true)
                     ,array("url"=>"search"                  ,"text"=>"Search"                ,"entity"=>""                   ,"action"=>true)
                     ,array("url"=>"personSkillMatrix"       ,"text"=>"Company Skill Matrix"  ,"entity"=>"person"             ,"action"=>true)
                     ,array("url"=>"personSkillAdd"          ,"text"=>"Edit Skill Items"      ,"entity"=>"person"             ,"action"=>PERM_PERSON_READ_MANAGEMENT)
                     ,array("url"=>"commentTemplateList"     ,"text"=>"Comment Templates"     ,"entity"=>"commentTemplate","action"=>PERM_READ_WRITE)
                     ,array("url"=>"loans"                   ,"text"=>"Item Loans"            ,"entity"=>"loan"               ,"action"=>true)
                     ,array("url"=>"report"                  ,"text"=>"Reports"               ,"entity"=>""                   ,"action"=>true, "function"=>"has_report_perm")
                     ,array("url"=>"backup"                  ,"text"=>"Database & File Backup","entity"=>""                   ,"function"=>"has_backup_perm")
                     ,array("url"=>"sourceCodeList"          ,"text"=>"allocPSA Source Code"      ,"entity"=>"")
                     );

  //,array("url"=>"stats"                   ,"text"=>"allocPSA Statistics"   ,"entity"=>"config"             ,"action"=>PERM_UPDATE)


$finance_options = array(array("url"=>"tf", "params"=>"", "text"=>"New Tagged Fund", "entity"=>"tf", "action"=>PERM_CREATE)
                        ,array("url"=>"tfList", "text"=>"List of Tagged Funds", "entity"=>"tf", "action"=>PERM_READ, "br"=>true)
                        ,array("url"=>"transaction", "params"=>"", "text"=>"New Transaction", "entity"=>"transaction", "action"=>PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION)
                        ,array("url"=>"searchTransaction", "params"=>"", "text"=>"Search Transactions", "entity"=>"transaction", "action"=>PERM_READ, "br"=>true)
                        ,array("url"=>"expenseForm", "text"=>"New Expense Form", "entity"=>"expenseForm", "action"=>PERM_CREATE)
                        ,array("url"=>"expenseFormList", "params"=>"", "text"=>"View Pending Expenses", "entity"=>"expenseForm", "action"=>PERM_READ, "br"=>true)
                        ,array("url"=>"wagesUpload", "params"=>"", "text"=>"Upload Wages File", "entity"=>"transaction", "action"=>PERM_FINANCE_WRITE_WAGE_TRANSACTION, "br"=>true)
                        ,array("url"=>"transactionRepeat", "params"=>"", "text"=>"New Repeating Expense", "entity"=>"transaction", "action"=>PERM_READ)
                        ,array("url"=>"transactionRepeatList", "params"=>"", "text"=>"Repeating Expense List", "entity"=>"transaction", "action"=>PERM_READ)
                        ,array("url"=>"checkRepeat", "params"=>"", "text"=>"Execute Repeating Expenses", "entity"=>"transaction", "action"=>PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT)
                        );


                        #,array("url"=>"reconciliationReport", "params"=>"", "text"=>"Reconciliation Report", "entity"=>"transaction", "action"=>PERM_FINANCE_RECONCILIATION_REPORT)
function show_misc_options($template) {
  global $misc_options, $TPL;

  $TPL["br"] = "<br>\n";
  reset($misc_options);
  while (list(, $option) = each($misc_options)) {
    if ($option["entity"] != "") {
      if (have_entity_perm($option["entity"], $option["action"], $current_user, true)) {
        $TPL["url"] = $TPL["url_alloc_".$option["url"]];
        $TPL["params"] = $option["params"];
        $TPL["text"] = $option["text"];
        include_template($template);
      }
    } else if ($option["function"]){
      $f = $option["function"];

      if ($f()) {
        $TPL["url"] = $TPL["url_alloc_".$option["url"]];
        $TPL["params"] = $option["params"];
        $TPL["text"] = $option["text"];
        include_template($template);
      }
    } else {
      $TPL["url"] = $TPL["url_alloc_".$option["url"]];
      $TPL["params"] = $option["params"];
      $TPL["text"] = $option["text"];
      include_template($template);
    }
  }
}

function show_finance_options($template) {
  global $finance_options, $TPL;
  foreach ($finance_options as $option) {
    if (have_entity_perm($option["entity"], $option["action"], $current_user, true)) {
      $TPL["url"] = $TPL["url_alloc_".$option["url"]];
      $TPL["params"] = $option["params"];
      $TPL["text"] = $option["text"];
      $TPL["br"] = "";
      if ($option["br"]) {
        $TPL["br"] = "<br><br>\n";
      } else {
        $TPL["br"] = "<br>\n";
      }

      include_template($template);
    }
  }
}



$TPL["main_alloc_title"] = "Tools - ".APPLICATION_NAME;

include_template("templates/menuM.tpl");



?>
