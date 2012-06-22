<?php

// Unescape everything in the database. Previously, due to magic_quotes_gpc
// being left on, everything in the database had a single layer of escaping. The
// default preferred setting is now for it to be off. This patch modifies
// everything in the DB to comply. Any errors are probably not fatal, but you may
// wish to look over the data to ensure it still looks correct.
// BACKUP YOUR DATABASE BEFORE RUNNING THIS PATCH.

ini_set('max_execution_time',600);

$fields["absence"]=array(
 "contactDetails"
);
$fields["announcement"]=array(
 "heading"
, "body"
);
$fields["client"]=array(
 "clientName"
, "clientStreetAddressOne"
, "clientStreetAddressTwo"
, "clientSuburbOne"
, "clientSuburbTwo"
, "clientStateOne"
, "clientStateTwo"
, "clientPostcodeOne"
, "clientPostcodeTwo"
, "clientPhoneOne"
, "clientFaxOne"
, "clientCountryOne"
, "clientCountryTwo"
, "clientComment"
, "clientCreatedTime"
);
$fields["clientContact"]=array(
 "clientContactName"
, "clientContactStreetAddress"
, "clientContactSuburb"
, "clientContactState"
, "clientContactPostcode"
, "clientContactPhone"
, "clientContactMobile"
, "clientContactFax"
, "clientContactEmail"
, "clientContactOther"
, "clientContactCountry"
);
$fields["comment"]=array(
 "commentType"
, "commentCreatedUserText"
, "commentEmailRecipients"
, "comment"
);
$fields["config"]=array(
 "name"
, "value"
, "type"
);
$fields["expenseForm"]=array(
 "paymentMethod"
, "expenseFormComment"
);
$fields["history"]=array(
 "the_place"
, "the_args"
, "the_label"
);
$fields["htmlElement"]=array(
 "handle"
, "label"
, "helpText"
, "defaultValue"
);
$fields["htmlAttribute"]=array(
 "name"
, "value"
);
$fields["htmlElementType"]=array(
 "handle"
, "name"
, "valueAttributeName"
);
$fields["htmlAttributeType"]=array(
 "name"
, "defaultValue"
);
$fields["invoice"]=array(
 "invoiceName"
);
$fields["invoiceItem"]=array(
 "iiMemo"
);
$fields["item"]=array(
 "itemName"
, "itemNotes"
, "itemAuthor"
);
$fields["loan"]=array(
);
$fields["patchLog"]=array(
 "patchName"
, "patchDesc"
);
$fields["permission"]=array(
  "tableName"
, "roleName"
, "comment"
);
$fields["person"]=array(
 "username"
, "password"
, "perms"
, "emailAddress"
, "availability"
, "areasOfInterest"
, "comments"
, "managementComments"
, "emailFormat"
, "firstName"
, "surname"
, "dailyTaskEmail"
, "sessData"
, "phoneNo1"
, "phoneNo2"
, "emergencyContact"
);
$fields["project"]=array(
 "projectName"
, "projectComments"
, "projectClientName"
, "projectClientPhone"
, "projectClientMobile"
, "projectClientEMail"
, "projectClientAddress"
, "projectShortName"
);
$fields["projectCommissionPerson"]=array(
);
$fields["projectModificationNote"]=array(
 "modDescription"
);
$fields["projectPerson"]=array(
 "emailDateRegex"
);
$fields["projectPersonRole"]=array(
 "projectPersonRoleName"
, "projectPersonRoleHandle"
);
$fields["reminder"]=array(
 "reminderType"
, "reminderSubject"
, "reminderContent"
);
$fields["sentEmailLog"]=array(
 "sentEmailTo"
, "sentEmailSubject"
, "sentEmailBody"
, "sentEmailHeader"
);
$fields["sess"]=array(
 "sessID"
, "sessData"
);
$fields["skillList"]=array(
 "skillName"
, "skillDescription"
, "skillClass"
);
$fields["skillProficiencys"]=array(
);
$fields["task"]=array(
 "taskName"
, "taskDescription"
, "taskComments"
);
$fields["taskCCList"]=array(
 "fullName"
, "emailAddress"
);
$fields["taskCommentTemplate"]=array(
 "taskCommentTemplateName"
, "taskCommentTemplateText"
);
$fields["taskType"]=array(
 "taskTypeName"
);
$fields["tf"]=array(
 "tfName"
, "tfComments"
, "quickenAccount"
);
$fields["tfPerson"]=array(
);
$fields["timeSheet"]=array(
 "billingNote"
);
$fields["timeSheetItem"]=array(
 "description"
, "location"
, "comment"
);
$fields["timeUnit"]=array(
 "timeUnitName"
, "timeUnitLabelA"
, "timeUnitLabelB"
);
$fields["token"]=array(
 "tokenHash"
, "tokenEntity"
);
$fields["tokenAction"]=array(
 "tokenAction"
, "tokenActionType"
, "tokenActionMethod"
);
$fields["transaction"]=array(
 "companyDetails"
, "product"
);
$fields["transactionRepeat"]=array(
 "payToName"
, "payToAccount"
, "companyDetails"
, "emailOne"
, "emailTwo"
, "paymentBasis"
, "product"
, "status"
);
$tables=array( "absence", "announcement", "client", "clientContact", "comment", "config", "expenseForm", "history", "htmlElement", "htmlAttribute", "htmlElementType", "htmlAttributeType", "invoice", "invoiceItem", "item", "loan", "patchLog", "permission", "person", "project", "projectCommissionPerson", "projectModificationNote", "projectPerson", "projectPersonRole", "reminder", "sentEmailLog", "skillList", "skillProficiencys", "task", "taskCCList", "taskCommentTemplate", "taskType", "tf", "tfPerson", "timeSheet", "timeSheetItem", "timeUnit", "token", "tokenAction", "transaction", "transactionRepeat");

$db = new db_alloc();
$db2 = new db_alloc();

foreach ($tables as $table) {
	$q = prepare("SELECT * FROM %s", $table);
	$db->query($q);
	if ($table == "skillList") {
		$keyField = "skillID";
	} else if ($table == "skillProficiencys") {
    $keyField = "proficiencyID";
  } else if ($table == "projectModificationNote") {
    $keyField = "projectModNoteID";
	} else {
		$keyField = $table."ID";
  } 

  while ($row = $db->row()) {
    $q = prepare("UPDATE %s SET ", $table);
    $join = "";
    $update = False;
    foreach ($fields[$table] as $field) {
        /* history.the_label was double escaped before. */
        if ($table == "history" && $field == "the_label") {
          $q.= prepare($join."$field = \"%s\"",	stripslashes(stripslashes($row[$field])));
        } else {
          $q.= prepare($join."$field = \"%s\"",	stripslashes($row[$field]));
        }
        $join = ", ";
        $update = True;
        /*      }*/
    }
    $q.= prepare(" WHERE %s = %d;",	$keyField, $row[$keyField]);
    $update and $db2->query($q);
	}
}

?>
