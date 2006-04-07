<?php
require_once("alloc.inc");

$typeOptions = array("salary", "commission", "timesheet", "adjustment", "insurance");
$TPL["typeOptions"] = get_options_from_array($typeOptions, $type, false);

$permissions = explode(",", $current_user->get_value("perms"));
if (!in_array("admin", $permissions) && !in_array("manage", $permissions)) {
  $tfID = $current_user->get_id();
  $transactionModifiedUser = $current_user->get_id();
  $TPL["tfIDOptions"] = $current_user->get_value("username");
} else {
  $db = new db_alloc;
  $tf = new tf;
  $tfIDOptions = array();
  $query = "SELECT * FROM tf ORDER BY tfName";
  $db->query($query);
  while ($db->next_record()) {
    $tf->read_db_record($db);
    $tfIDOptions[$tf->get_id()] = $tf->get_value("tfName");
  }
  $TPL["tfIDOptions"] = "<select name=\"tfID\"><option value=\"\"> -- ALL -- ".get_options_from_array($tfIDOptions, $tfID, true)."</select>";
}

$db = new db_alloc;
$db->query("SELECT projectName, projectID FROM project WHERE projectStatus = 'current' ORDER BY projectName");
$TPL["projectIDOptions"].= get_options_from_db($db, "projectName", "projectID", $projectID);

include_template("templates/transactionPendingListM.tpl");

function show_transaction_list($template_name) {
  global $db, $dbTwo, $type, $tfID, $projectID, $transactionModifiedUser, $sort, $TPL;

  $db = new db_alloc;
  // $dbTwo = new db_alloc;
  $transaction = new transaction;
  $tf = new tf;
  $person = new person;

  $query =
    "SELECT transaction.*,person.username,tf.tfName,project.projectName FROM transaction"." LEFT JOIN person ON transaction.transactionModifiedUser = person.personID"." LEFT JOIN tf ON transaction.tfID = tf.tfID"." LEFT JOIN project ON transaction.projectID = project.projectID".
    " WHERE status = 'pending'"." AND transactionType != 'invoice' AND transactionType != 'expense'";
  if (isset($type) && $type != "") {
    $query.= " AND transaction.transactionType = '$type'";
  }
  if (isset($tfID) && $tfID != "") {
    $query.= " AND transaction.tfID = '$tfID'";
  }
  if (isset($projectID) && $projectID != "") {
    $query.= " AND transaction.projectID = '$projectID'";
  }
  if (isset($transactionModifiedUser) && $transactionModifiedUser != "") {
    $query.= " AND transaction.transactionModifiedUser = '$transactionModifiedUser'";
  }
  if (isset($sort)) {
    $query.= " ORDER BY $sort,transactionID";
  } else {
    $query.= " ORDER BY transactionID";
  }

  $db->query($query);
  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

    $transaction->read_db_record($db);
    $transaction->set_tpl_values();
    $person->read_db_record($db);
    $tf->read_db_record($db);

    $project = new project;
    $project->read_db_record($db);

    $TPL["amount"] = number_format(-$transaction->get_value("amount"), 2);
    $TPL["lastModified"] = get_mysql_date_stamp($transaction->get_value("lastModified"));

    // $dbTwo->query("SELECT tfName FROM tf WHERE tfID=" . $transaction->get_value("tfID"));
    // $dbTwo->next_record();
    // $TPL["tfName"] = $dbTwo->f("tfName");
    $TPL["tfName"] = $tf->get_value("tfName");

    $TPL["projectName"] = $project->get_value("projectName");

    // $dbTwo->query("SELECT username FROM person WHERE personID=" . $transaction->get_value("transactionModifiedUser"));
    // $dbTwo->next_record();
    // $TPL["transactionModifiedUser"] = $dbTwo->f("username");
    $TPL["transactionModifiedUser"] = $person->get_value("username");

    if ($transaction->get_value("transactionType") == "timesheet") {
      $TPL["transactionType"] = "<a href=\"".$TPL["url_alloc_timeSheet"]
        ."&timeSheetID=".$transaction->get_value("timeSheetID")."\">timesheet</a>";
    }

    include_template($template_name);
  }

  page_close();
}




?>
