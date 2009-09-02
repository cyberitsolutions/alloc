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



// This file basically provides static template values that are used throughout the application and is included by alloc.php
$TPL2 = array("url_alloc_attachments_dir"               => ATTACHMENTS_DIR
            ,"url_alloc_logout"                         => $sess->url(SCRIPT_PATH."login/logout.php")
            ,"url_alloc_home"                           => $sess->url(SCRIPT_PATH."home/home.php")
            ,"url_alloc_history"                        => $sess->url(SCRIPT_PATH."home/history.php")
            ,"url_alloc_getDoc"                         => $sess->url(SCRIPT_PATH."shared/get_attachment.php")
            ,"url_alloc_delDoc"                         => $sess->url(SCRIPT_PATH."shared/del_attachment.php")
            ,"url_alloc_exportDoc"                      => $sess->url(SCRIPT_PATH."shared/get_export.php")
            ,"url_alloc_patch"                          => $sess->url(SCRIPT_PATH."installation/patch.php")
            ,"url_alloc_menuSubmit"                     => $sess->url(SCRIPT_PATH."shared/menuSubmit.php")

            ,"url_alloc_project"                        => $sess->url(SCRIPT_PATH."project/project.php")
            ,"url_alloc_projectList"                    => $sess->url(SCRIPT_PATH."project/projectList.php")
            ,"url_alloc_projectGraph"                   => $sess->url(SCRIPT_PATH."project/projectGraph.php")
            ,"url_alloc_projectGraphImage"              => $sess->url(SCRIPT_PATH."project/projectGraphImage.php")
            ,"url_alloc_saveProjectPerson"              => $sess->url(SCRIPT_PATH."project/saveProjectPerson.php")
            ,"url_alloc_projectPerson"                  => $sess->url(SCRIPT_PATH."project/projectPerson.php")
            ,"url_alloc_updateCopyProjectList"          => $sess->url(SCRIPT_PATH."project/updateProjectList.php")
            ,"url_alloc_updateProjectClientList"        => $sess->url(SCRIPT_PATH."project/updateProjectClientList.php")
            ,"url_alloc_updateProjectClientContactList" => $sess->url(SCRIPT_PATH."project/updateProjectClientContactList.php")
            ,"url_alloc_personGraph"                    => $sess->url(SCRIPT_PATH."project/personGraph.php")
            ,"url_alloc_personGraphImage"               => $sess->url(SCRIPT_PATH."project/personGraphImage.php")

            ,"url_alloc_task"                           => $sess->url(SCRIPT_PATH."task/task.php")
            ,"url_alloc_updateParentTasks"              => $sess->url(SCRIPT_PATH."task/updateParentTasks.php")
            ,"url_alloc_updateInterestedParties"        => $sess->url(SCRIPT_PATH."task/updateInterestedParties.php")
            ,"url_alloc_updateCommentTemplate"          => $sess->url(SCRIPT_PATH."comment/updateCommentTemplate.php")
            ,"url_alloc_updateProjectList"              => $sess->url(SCRIPT_PATH."task/updateProjectList.php")
            ,"url_alloc_taskList"                       => $sess->url(SCRIPT_PATH."task/taskList.php")
            ,"url_alloc_commentTemplate"                => $sess->url(SCRIPT_PATH."comment/commentTemplate.php")
            ,"url_alloc_commentTemplateList"            => $sess->url(SCRIPT_PATH."comment/commentTemplateList.php")
            ,"url_alloc_taskCalendar"                   => $sess->url(SCRIPT_PATH."task/taskCalendar.php")
            ,"url_alloc_updatePersonList"               => $sess->url(SCRIPT_PATH."task/updatePersonList.php")
            ,"url_alloc_updateManagerPersonList"        => $sess->url(SCRIPT_PATH."task/updateManagerPersonList.php")
 	          ,"url_alloc_updateTaskDupes"                => $sess->url(SCRIPT_PATH."task/updateTaskDupes.php")

            ,"url_alloc_comment"                        => $sess->url(SCRIPT_PATH."comment/comment.php")
            ,"url_alloc_downloadEmail"                  => $sess->url(SCRIPT_PATH."email/downloadEmail.php")
            ,"url_alloc_client"                         => $sess->url(SCRIPT_PATH."client/client.php")
            ,"url_alloc_clientList"                     => $sess->url(SCRIPT_PATH."client/clientList.php")
            ,"url_alloc_personList"                     => $sess->url(SCRIPT_PATH."person/personList.php")
            ,"url_alloc_personSkillAdd"                 => $sess->url(SCRIPT_PATH."person/personSkillAdd.php")
            ,"url_alloc_person"                         => $sess->url(SCRIPT_PATH."person/person.php")
            ,"url_alloc_personSkillMatrix"              => $sess->url(SCRIPT_PATH."person/personSkillMatrix.php")
            ,"url_alloc_timeSheet"                      => $sess->url(SCRIPT_PATH."time/timeSheet.php")
            ,"url_alloc_timeSheetItem"                  => $sess->url(SCRIPT_PATH."time/timeSheetItem.php")
            ,"url_alloc_timeSheetPrint"                 => $sess->url(SCRIPT_PATH."time/timeSheetPrint.php")
            ,"url_alloc_timeSheetList"                  => $sess->url(SCRIPT_PATH."time/timeSheetList.php")
            ,"url_alloc_updateTimeSheetTaskList"        => $sess->url(SCRIPT_PATH."time/updateTimeSheetTaskList.php")
            ,"url_alloc_weeklyTime"                     => $sess->url(SCRIPT_PATH."time/weeklyTime.php")
            ,"url_alloc_updateProjectListByClient"      => $sess->url(SCRIPT_PATH."time/updateProjectListByClient.php")
            ,"url_alloc_financeMenu"                    => $sess->url(SCRIPT_PATH."finance/menu.php")
            ,"url_alloc_tfList"                         => $sess->url(SCRIPT_PATH."finance/tfList.php")
            ,"url_alloc_expenseForm"                    => $sess->url(SCRIPT_PATH."finance/expenseForm.php")
            ,"url_alloc_expenseFormList"                => $sess->url(SCRIPT_PATH."finance/expenseFormList.php")
            ,"url_alloc_transactionRepeatList"          => $sess->url(SCRIPT_PATH."finance/transactionRepeatList.php")
            ,"url_alloc_transactionRepeat"              => $sess->url(SCRIPT_PATH."finance/transactionRepeat.php")
            ,"url_alloc_checkRepeat"                    => $sess->url(SCRIPT_PATH."finance/checkRepeat.php")
            ,"url_alloc_tf"                             => $sess->url(SCRIPT_PATH."finance/tf.php")
            ,"url_alloc_transactionList"                => $sess->url(SCRIPT_PATH."finance/transactionList.php")
            ,"url_alloc_transactionPendingList"         => $sess->url(SCRIPT_PATH."finance/transactionPendingList.php")
            ,"url_alloc_searchTransaction"              => $sess->url(SCRIPT_PATH."finance/searchTransaction.php")
            ,"url_alloc_transaction"                    => $sess->url(SCRIPT_PATH."finance/transaction.php")
            ,"url_alloc_transactionGroup"               => $sess->url(SCRIPT_PATH."finance/transactionGroup.php")
            ,"url_alloc_loans"                          => $sess->url(SCRIPT_PATH."item/itemLoan.php")
            ,"url_alloc_loanAndReturn"                  => $sess->url(SCRIPT_PATH."item/loanAndReturn.php")
            ,"url_alloc_addItem"                        => $sess->url(SCRIPT_PATH."item/addItem.php")
            ,"url_alloc_item"                           => $sess->url(SCRIPT_PATH."item/item.php")
            ,"url_alloc_wagesUpload"                    => $sess->url(SCRIPT_PATH."finance/wagesUpload.php")
            ,"url_alloc_reconciliationReport"           => $sess->url(SCRIPT_PATH."finance/reconciliationReport.php")
            ,"url_alloc_expenseUpload"                  => $sess->url(SCRIPT_PATH."finance/expenseUpload.php")
            ,"url_alloc_expenseUploadResults"           => $sess->url(SCRIPT_PATH."finance/expenseUploadResults.php")
            ,"url_alloc_invoicesUpload"                 => $sess->url(SCRIPT_PATH."invoice/invoicesUpload.php")
            ,"url_alloc_invoiceList"                    => $sess->url(SCRIPT_PATH."invoice/invoiceList.php")
            ,"url_alloc_invoice"                        => $sess->url(SCRIPT_PATH."invoice/invoice.php")
            ,"url_alloc_config"                         => $sess->url(SCRIPT_PATH."config/config.php")
            ,"url_alloc_configEdit"                     => $sess->url(SCRIPT_PATH."config/configEdit.php")
            ,"url_alloc_metaEdit"                       => $sess->url(SCRIPT_PATH."config/metaEdit.php")
            ,"url_alloc_configHtml"                     => $sess->url(SCRIPT_PATH."config/configHtml.php")
            ,"url_alloc_configHtmlList"                 => $sess->url(SCRIPT_PATH."config/configHtmlList.php")
            ,"url_alloc_absence"                        => $sess->url(SCRIPT_PATH."person/absence.php")
            ,"url_alloc_announcementList"               => $sess->url(SCRIPT_PATH."announcement/announcementList.php")
            ,"url_alloc_announcement"                   => $sess->url(SCRIPT_PATH."announcement/announcement.php")
            ,"url_alloc_reminderList"                   => $sess->url(SCRIPT_PATH."reminder/reminderList.php")
            ,"url_alloc_reminderAdd"                    => $sess->url(SCRIPT_PATH."reminder/reminderAdd.php")
            ,"url_alloc_permissionList"                 => $sess->url(SCRIPT_PATH."security/permissionList.php")
            ,"url_alloc_permission"                     => $sess->url(SCRIPT_PATH."security/permission.php")
            ,"url_alloc_search"                         => $sess->url(SCRIPT_PATH."search/search.php")
            ,"url_alloc_report"                         => $sess->url(SCRIPT_PATH."report/report.php")
            ,"url_alloc_tools"                          => $sess->url(SCRIPT_PATH."tools/menu.php")
            ,"url_alloc_wiki"                           => $sess->url(SCRIPT_PATH."wiki/wiki.php")
            ,"url_alloc_stats"                          => $sess->url(SCRIPT_PATH."tools/stats.php")
            ,"url_alloc_statsImage"                     => $sess->url(SCRIPT_PATH."tools/statsImage.php")
            ,"url_alloc_costtime"                       => $sess->url(SCRIPT_PATH."tools/costtime.php")
            ,"url_alloc_backup"                         => $sess->url(SCRIPT_PATH."tools/backup.php")
            ,"url_alloc_helpfile"                       => $sess->url(SCRIPT_PATH."help/help.html")
            ,"url_alloc_getHelp"                        => $sess->url(SCRIPT_PATH."help/getHelp.php")
            ,"url_alloc_sourceCodeList"                 => $sess->url(SCRIPT_PATH."tools/sourceCodeList.php")
            ,"url_alloc_sourceCodeView"                 => $sess->url(SCRIPT_PATH."tools/sourceCodeView.php")
            ,"url_alloc_product"                        => $sess->url(SCRIPT_PATH."sale/product.php")
            ,"url_alloc_productList"                    => $sess->url(SCRIPT_PATH."sale/productList.php")
            ,"url_alloc_productSale"                    => $sess->url(SCRIPT_PATH."sale/productSale.php")
            ,"url_alloc_productSaleList"                => $sess->url(SCRIPT_PATH."sale/productSaleList.php")
            ,"url_alloc_updateCostPrice"                => $sess->url(SCRIPT_PATH."sale/updateCostPrice.php")
            ,"url_alloc_fileTree"                       => $sess->url(SCRIPT_PATH."wiki/fileTree.php")
            ,"url_alloc_file"                           => $sess->url(SCRIPT_PATH."wiki/file.php")
            ,"url_alloc_fileHistory"                    => $sess->url(SCRIPT_PATH."wiki/fileHistory.php")
            ,"url_alloc_filePreview"                    => $sess->url(SCRIPT_PATH."wiki/filePreview.php")
            ,"url_alloc_fileDownload"                   => $sess->url(SCRIPT_PATH."wiki/fileDownload.php")

);

$TPL = array_merge($TPL,$TPL2);



?>
