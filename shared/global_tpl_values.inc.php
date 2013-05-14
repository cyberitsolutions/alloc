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



// This file basically provides static template values that are used throughout the application and is included by alloc.php
$alloc_urls = array(
             "url_alloc_logout"                         => "login/logout.php"
            ,"url_alloc_home"                           => "home/home.php"
            ,"url_alloc_history"                        => "home/history.php"
            ,"url_alloc_getDoc"                         => "shared/get_attachment.php"
            ,"url_alloc_delDoc"                         => "shared/del_attachment.php"
            ,"url_alloc_exportDoc"                      => "shared/get_export.php"
            ,"url_alloc_getMimePart"                    => "shared/get_mime_part.php"
            ,"url_alloc_patch"                          => "installation/patch.php"
            ,"url_alloc_menuSubmit"                     => "shared/menuSubmit.php"
            ,"url_alloc_logo"                           => "shared/logo.php"
            ,"url_alloc_star"                           => "shared/star.php"
            ,"url_alloc_starList"                       => "shared/starList.php"
            ,"url_alloc_settings"                       => "shared/settings.php"
            ,"url_alloc_inbox"                          => "email/inbox.php"
            ,"url_alloc_fetchBody"                      => "email/fetchBody.php"
            ,"url_alloc_updateTaskName"                 => "task/updateTaskName.php"

            ,"url_alloc_project"                        => "project/project.php"
            ,"url_alloc_projectList"                    => "project/projectList.php"
            ,"url_alloc_projectGraph"                   => "project/projectGraph.php"
            ,"url_alloc_projectGraphImage"              => "project/projectGraphImage.php"
            ,"url_alloc_saveProjectPerson"              => "project/saveProjectPerson.php"
            ,"url_alloc_projectPerson"                  => "project/projectPerson.php"
            ,"url_alloc_updateCopyProjectList"          => "project/updateProjectList.php"
            ,"url_alloc_updateProjectClientList"        => "project/updateProjectClientList.php"
            ,"url_alloc_updateProjectClientContactList" => "project/updateProjectClientContactList.php"
            ,"url_alloc_updateProjectPersonRate"        => "project/updateProjectPersonRate.php"
            ,"url_alloc_personGraph"                    => "project/personGraph.php"
            ,"url_alloc_personGraphImage"               => "project/personGraphImage.php"
            ,"url_alloc_importCSV"                      => "project/parseCSV.php"

            ,"url_alloc_task"                           => "task/task.php"
            ,"url_alloc_updateParentTasks"              => "task/updateParentTasks.php"
            ,"url_alloc_updateInterestedParties"        => "task/updateInterestedParties.php"
            ,"url_alloc_updateCommentTemplate"          => "comment/updateCommentTemplate.php"
            ,"url_alloc_updateRecipients"               => "comment/updateRecipients.php"
            ,"url_alloc_updateProjectList"              => "task/updateProjectList.php"
            ,"url_alloc_taskList"                       => "task/taskList.php"
            ,"url_alloc_taskListPrint"                  => "task/taskListPrint.php"
            ,"url_alloc_taskListCSV"                    => "task/taskListCSV.php"
            ,"url_alloc_commentTemplate"                => "comment/commentTemplate.php"
            ,"url_alloc_commentTemplateList"            => "comment/commentTemplateList.php"
            ,"url_alloc_taskCalendar"                   => "calendar/calendar.php"
            ,"url_alloc_updatePersonList"               => "task/updatePersonList.php"
            ,"url_alloc_updateManagerPersonList"        => "task/updateManagerPersonList.php"
            ,"url_alloc_updateEstimatorPersonList"      => "task/updateEstimatorPersonList.php"
 	          ,"url_alloc_updateTaskDupes"                => "task/updateTaskDupes.php"

            ,"url_alloc_comment"                        => "comment/comment.php"
            ,"url_alloc_commentSummary"                 => "comment/summary.php"
            ,"url_alloc_downloadEmail"                  => "email/downloadEmail.php"
            ,"url_alloc_downloadComments"               => "email/downloadComments.php"
            ,"url_alloc_client"                         => "client/client.php"
            ,"url_alloc_clientList"                     => "client/clientList.php"
            ,"url_alloc_personList"                     => "person/personList.php"
            ,"url_alloc_personSkillAdd"                 => "person/personSkillAdd.php"
            ,"url_alloc_person"                         => "person/person.php"
            ,"url_alloc_personSkillMatrix"              => "person/personSkillMatrix.php"
            ,"url_alloc_timeSheet"                      => "time/timeSheet.php"
            ,"url_alloc_timeSheetItem"                  => "time/timeSheetItem.php"
            ,"url_alloc_timeSheetPrint"                 => "time/timeSheetPrint.php"
            ,"url_alloc_timeSheetList"                  => "time/timeSheetList.php"
            ,"url_alloc_timeSheetGraph"                 => "time/timeSheetGraph.php"
            ,"url_alloc_updateTsiHintHome"              => "time/updateTsiHintHome.php"
            ,"url_alloc_updateTimeSheetHome"            => "time/updateTimeSheetHome.php"
            ,"url_alloc_updateTimeSheetTaskList"        => "time/updateTimeSheetTaskList.php"
            ,"url_alloc_updateTimeSheetProjectList"     => "time/updateProjectListByStatus.php"
            ,"url_alloc_weeklyTime"                     => "time/weeklyTime.php"
            ,"url_alloc_updateProjectListByClient"      => "time/updateProjectListByClient.php"
            ,"url_alloc_financeMenu"                    => "finance/menu.php"
            ,"url_alloc_tfList"                         => "finance/tfList.php"
            ,"url_alloc_expenseForm"                    => "finance/expenseForm.php"
            ,"url_alloc_expenseFormList"                => "finance/expenseFormList.php"
            ,"url_alloc_transactionRepeatList"          => "finance/transactionRepeatList.php"
            ,"url_alloc_transactionRepeat"              => "finance/transactionRepeat.php"
            ,"url_alloc_checkRepeat"                    => "finance/checkRepeat.php"
            ,"url_alloc_tf"                             => "finance/tf.php"
            ,"url_alloc_transactionList"                => "finance/transactionList.php"
            ,"url_alloc_transactionPendingList"         => "finance/transactionPendingList.php"
            ,"url_alloc_searchTransaction"              => "finance/searchTransaction.php"
            ,"url_alloc_transaction"                    => "finance/transaction.php"
            ,"url_alloc_transactionGroup"               => "finance/transactionGroup.php"
            ,"url_alloc_updateTFList"                   => "finance/updateTFList.php"
            ,"url_alloc_loans"                          => "item/itemLoan.php"
            ,"url_alloc_loanAndReturn"                  => "item/loanAndReturn.php"
            ,"url_alloc_addItem"                        => "item/addItem.php"
            ,"url_alloc_item"                           => "item/item.php"
            ,"url_alloc_wagesUpload"                    => "finance/wagesUpload.php"
            ,"url_alloc_reconciliationReport"           => "finance/reconciliationReport.php"
            ,"url_alloc_expenseUpload"                  => "finance/expenseUpload.php"
            ,"url_alloc_expenseUploadResults"           => "finance/expenseUploadResults.php"
            ,"url_alloc_invoiceList"                    => "invoice/invoiceList.php"
            ,"url_alloc_invoice"                        => "invoice/invoice.php"
            ,"url_alloc_invoiceRepeat"                  => "invoice/invoiceRepeat.php"
            ,"url_alloc_invoicePrint"                   => "invoice/invoicePrint.php"
            ,"url_alloc_config"                         => "config/config.php"
            ,"url_alloc_configEdit"                     => "config/configEdit.php"
            ,"url_alloc_metaEdit"                       => "config/metaEdit.php"
            ,"url_alloc_configHtml"                     => "config/configHtml.php"
            ,"url_alloc_configHtmlList"                 => "config/configHtmlList.php"
            ,"url_alloc_absence"                        => "person/absence.php"
            ,"url_alloc_announcementList"               => "announcement/announcementList.php"
            ,"url_alloc_announcement"                   => "announcement/announcement.php"
            ,"url_alloc_reminderList"                   => "reminder/reminderList.php"
            ,"url_alloc_reminder"                       => "reminder/reminder.php"
            ,"url_alloc_permissionList"                 => "security/permissionList.php"
            ,"url_alloc_permission"                     => "security/permission.php"
            ,"url_alloc_search"                         => "search/search.php"
            ,"url_alloc_report"                         => "report/report.php"
            ,"url_alloc_tools"                          => "tools/menu.php"
            ,"url_alloc_wiki"                           => "wiki/wiki.php"
            ,"url_alloc_stats"                          => "tools/stats.php"
            ,"url_alloc_statsImage"                     => "tools/statsImage.php"
            ,"url_alloc_costtime"                       => "tools/costtime.php"
            ,"url_alloc_backup"                         => "tools/backup.php"
            ,"url_alloc_whatsnew"                       => "tools/whatsnew.php"
            ,"url_alloc_helpfile"                       => "help/help.html"
            ,"url_alloc_getHelp"                        => "help/getHelp.php"
            ,"url_alloc_sourceCodeList"                 => "tools/sourceCodeList.php"
            ,"url_alloc_sourceCodeView"                 => "tools/sourceCodeView.php"
            ,"url_alloc_product"                        => "sale/product.php"
            ,"url_alloc_productList"                    => "sale/productList.php"
            ,"url_alloc_productSale"                    => "sale/productSale.php"
            ,"url_alloc_productSaleList"                => "sale/productSaleList.php"
            ,"url_alloc_updateCostPrice"                => "sale/updateCostPrice.php"
            ,"url_alloc_fileTree"                       => "wiki/fileTree.php"
            ,"url_alloc_file"                           => "wiki/file.php"
            ,"url_alloc_fileHistory"                    => "wiki/fileHistory.php"
            ,"url_alloc_filePreview"                    => "wiki/filePreview.php"
            ,"url_alloc_fileDownload"                   => "wiki/fileDownload.php"
            ,"url_alloc_directory"                      => "wiki/directory.php"

);


?>
