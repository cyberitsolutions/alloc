<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

// This file provides basically static template values that are used throughout the application and is called by local.inc.php
$sess = Session::GetSession();

$TPL =array("url_alloc_index"=>SCRIPT_PATH."index.php",
 "url_alloc_login"=>SCRIPT_PATH."login/login.php",
 "url_alloc_logout"=>$sess->url(SCRIPT_PATH."login/logout.php"),
 "url_alloc_home"=>$sess->url(SCRIPT_PATH."home/home.php"),
 "url_alloc_history"=>$sess->url(SCRIPT_PATH."home/history.php"),
 "url_alloc_project" =>$sess->url(SCRIPT_PATH."project/project.php"),
 "url_alloc_task"=>$sess->url(SCRIPT_PATH."project/task.php"),
 "url_alloc_projectList"=>$sess->url(SCRIPT_PATH."project/projectList.php"),
 "url_alloc_projectSummary" =>$sess->url(SCRIPT_PATH."project/projectSummary.php"),
 "url_alloc_projectGraph"=>$sess->url(SCRIPT_PATH."project/projectGraph.php"),
 "url_alloc_updateParentTasks"=>$sess->url(SCRIPT_PATH."project/updateParentTasks.php"),
 "url_alloc_updateTaskCCList"=>$sess->url(SCRIPT_PATH."project/updateTaskCCList.php"),
 "url_alloc_updateTaskCommentTemplate"=>$sess->url(SCRIPT_PATH."project/updateTaskCommentTemplate.php"),
 "url_alloc_updateProjectList"=>$sess->url(SCRIPT_PATH."project/updateProjectList.php"),
 "url_alloc_projectDoc" =>$sess->url(SCRIPT_PATH."project/projectDoc.php"),
 "url_alloc_clientDoc" =>$sess->url(SCRIPT_PATH."client/clientDoc.php"),
 "url_alloc_projectDocs_dir" =>ATTACHMENTS_DIR."projects/",
 "url_alloc_clientDocs_dir"=>ATTACHMENTS_DIR."clients/",
 "url_alloc_taskSummary"=>$sess->url(SCRIPT_PATH."project/taskSummary.php"),
 "url_alloc_saveProjectPerson"=>$sess->url(SCRIPT_PATH."project/saveProjectPerson.php"),
 "url_alloc_projectPerson" =>$sess->url(SCRIPT_PATH."project/projectPerson.php"),
 "url_alloc_taskComment" =>$sess->url(SCRIPT_PATH."project/taskComment.php"),
 "url_alloc_taskCommentTemplate" =>$sess->url(SCRIPT_PATH."project/taskCommentTemplate.php"),
 "url_alloc_taskCommentTemplateList" =>$sess->url(SCRIPT_PATH."project/taskCommentTemplateList.php"),
 "url_alloc_client"=>$sess->url(SCRIPT_PATH."client/client.php"),
 "url_alloc_clientList"=>$sess->url(SCRIPT_PATH."client/clientList.php"),
 "url_alloc_personList" =>$sess->url(SCRIPT_PATH."person/personList.php"),
 "url_alloc_personSkillAdd"=>$sess->url(SCRIPT_PATH."person/personSkillAdd.php"),
 "url_alloc_person"=>$sess->url(SCRIPT_PATH."person/person.php"),
 "url_alloc_personProcessor"=>$sess->url(SCRIPT_PATH."person/personProcessor.php"),
 "url_alloc_emailProcessor" =>$sess->url(SCRIPT_PATH."person/emailProcessor.php"),
 "url_alloc_personGraphs"=>$sess->url(SCRIPT_PATH."project/personGraphs.php"),
 "url_alloc_personGraphImage"=>$sess->url(SCRIPT_PATH."project/personGraphImage.php"),
 "url_alloc_personSkillMatrix" =>$sess->url(SCRIPT_PATH."person/personSkillMatrix.php"),
 "url_alloc_timeSheet"=>$sess->url(SCRIPT_PATH."time/timeSheet.php"),
 "url_alloc_timeSheetList"=>$sess->url(SCRIPT_PATH."time/timeSheetList.php"),
 "url_alloc_updateTimeSheetTaskList"=>$sess->url(SCRIPT_PATH."time/updateTimeSheetTaskList.php"),
 "url_alloc_weeklyTime" =>$sess->url(SCRIPT_PATH."time/weeklyTime.php"),
 "url_alloc_financeMenu"=>$sess->url(SCRIPT_PATH."finance/menu.php"),
 "url_alloc_tfList"=>$sess->url(SCRIPT_PATH."finance/tfList.php"),
 "url_alloc_expOneOff"=>$sess->url(SCRIPT_PATH."finance/exp-one-off.php"),
 "url_alloc_expenseFormList"=>$sess->url(SCRIPT_PATH."finance/expenseFormList.php"),
 "url_alloc_transactionRepeatList" =>$sess->url(SCRIPT_PATH."finance/transactionRepeatList.php"),
 "url_alloc_transactionRepeat"=>$sess->url(SCRIPT_PATH."finance/transactionRepeat.php"),
 "url_alloc_checkRepeat"=>$sess->url(SCRIPT_PATH."finance/checkRepeat.php"),
 "url_alloc_tf" =>$sess->url(SCRIPT_PATH."finance/tf.php"),
 "url_alloc_transactionList"=>$sess->url(SCRIPT_PATH."finance/transactionList.php"),
 "url_alloc_transactionPendingList"=>$sess->url(SCRIPT_PATH."finance/transactionPendingList.php"),
 "url_alloc_searchTransaction" =>$sess->url(SCRIPT_PATH."finance/searchTransaction.php"),
 "url_alloc_searchInvoice"=>$sess->url(SCRIPT_PATH."finance/searchInvoice.php"),
 "url_alloc_transaction"=>$sess->url(SCRIPT_PATH."finance/transaction.php"),
 "url_alloc_loans" =>$sess->url(SCRIPT_PATH."item/itemLoan.php"),
 "url_alloc_loanAndReturn"=>$sess->url(SCRIPT_PATH."item/loanAndReturn.php"),
 "url_alloc_addItem"=>$sess->url(SCRIPT_PATH."item/addItem.php"),
 "url_alloc_item" =>$sess->url(SCRIPT_PATH."item/item.php"),
 "url_alloc_wagesUpload"=>$sess->url(SCRIPT_PATH."finance/wagesUpload.php"),
 "url_alloc_invoicesUpload"=>$sess->url(SCRIPT_PATH."finance/invoicesUpload.php"),
 "url_alloc_invoiceItemList" =>$sess->url(SCRIPT_PATH."finance/invoiceItemList.php"),
 "url_alloc_invoiceItem"=>$sess->url(SCRIPT_PATH."finance/invoiceItem.php"),
 "url_alloc_reconciliationReport"=>$sess->url(SCRIPT_PATH."finance/reconciliationReport.php"),
 "url_alloc_expenseUpload" =>$sess->url(SCRIPT_PATH."finance/expenseUpload.php"),
 "url_alloc_expenseUploadResults"=>$sess->url(SCRIPT_PATH."finance/expenseUploadResults.php"),
 "url_alloc_config"=>$sess->url(SCRIPT_PATH."config/config.php"),
 "url_alloc_absence" =>$sess->url(SCRIPT_PATH."person/absence.php"),
 "url_alloc_announcementList"=>$sess->url(SCRIPT_PATH."announcement/announcementList.php"),
 "url_alloc_announcement"=>$sess->url(SCRIPT_PATH."announcement/announcement.php"),
 "url_alloc_eventFilterList" =>$sess->url(SCRIPT_PATH."notification/eventFilterList.php"),
 "url_alloc_eventFilterAdd"=>$sess->url(SCRIPT_PATH."notification/eventFilterAdd.php"),
 "url_alloc_eventFilterDelete"=>$sess->url(SCRIPT_PATH."notification/eventFilterDelete.php"),
 "url_alloc_reminderAdd" =>$sess->url(SCRIPT_PATH."notification/reminderAdd.php"),
 "url_alloc_permissionList"=>$sess->url(SCRIPT_PATH."security/permissionList.php"),
 "url_alloc_permission"=>$sess->url(SCRIPT_PATH."security/permission.php"),
 "url_alloc_search" =>$sess->url(SCRIPT_PATH."search/search.php"),
 "url_alloc_report"=>$sess->url(SCRIPT_PATH."report/report.php"),
 "url_alloc_tools"=>$sess->url(SCRIPT_PATH."tools/menu.php"),
 "url_alloc_stats"=>$sess->url(SCRIPT_PATH."tools/stats.php"),
 "url_alloc_statsImage"=>$sess->url(SCRIPT_PATH."tools/statsImage.php"),
 "url_alloc_costtime" =>$sess->url(SCRIPT_PATH."tools/costtime.php"),
 "current_date"=>date("Y-m-d H:i:s"),
 "today"=>date("Y-m-d"),
 "alloc_help_link_name"=>end(array_slice(explode("/", $_SERVER["PHP_SELF"]), -2, 1)),
 "script_path"=>SCRIPT_PATH,
 "table_box" =>"<table align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\" width=\"100%\">",
 "table_box_border" =>"<table align=\"center\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\" width=\"100%\">",
 "table_box_home"=>"<table align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\" width=\"100%\">",
 "table_box_norm" =>"<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\">",
 "table_box_norm_r"=>"<table align=\"right\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\">",
 "table_box_norm_c" =>"<table align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\">",
 "table_box_norm_c_border" =>"<table align=\"center\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\" valign=\"top\">",
 "main_alloc_title"=>end(explode("/", $_SERVER["SCRIPT_NAME"])),
 "ALLOC_TITLE"=>config::get_config_item("companyName")." ".ALLOC_TITLE,
 "ALLOC_VERSION"=>ALLOC_VERSION,
 "url_alloc_stylesheets"=>SCRIPT_PATH."stylesheets/",
 "url_alloc_images"=>SCRIPT_PATH."images/"

);


?>
