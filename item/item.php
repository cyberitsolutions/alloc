<?php
require_once("alloc.inc");

$current_user->check_employee();

global $db, $itemID, $borrow, $today, $timePeriod;

$item = new item;
$loan = new loan;
$db = new db_alloc;
$db->query("select * from item where itemID=$itemID");
$db->next_record();
$item->read_db_record($db);
$item->set_tpl_values();

  // new crap
$permissions = explode(",", $current_user->get_value("perms"));
if (in_array("admin", $permissions) || in_array("manager", $permissions)) {
  $users = array();
  $_db = new db_alloc;
  $_db->query("SELECT * FROM person ORDER BY username");
  while ($_db->next_record()) {
    $person = new person;
    $person->read_db_record($_db);
    $users[$person->get_id()] = $person->get_value('username');
  }
  $TPL["userSelect"] = "<select name=\"userID\">".get_options_from_array($users, $current_user->get_id(), true)
    ."</select><br>\n";
} else {
  $TPL["userSelect"] = "";
}

$temp = mktime(0, 0, 0, date("m") + $timePeriod, date("d"), date("Y"));
$whenToReturn = date("Y", $temp)."-".date("m", $temp)."-".date("d", $temp);



$today = date("Y")."-".date("m")."-".date("d");

if ($loanID) {
$loan->set_id($loanID);
$loan->select();
}


if ($borrowItem) {
  $db->query("select * from loan where itemID=$itemID and dateReturned='0000-00-00'");

  if ($db->next_record()) {     // if the item is already borrowed
    header("location:".$TPL["url_alloc_item"]."&itemID=$itemID&badBorrow=true&error=already_borrowed");
    exit();
  } else {                      // else lets make a new loan!
    $loan = new loan;
    $loan->read_globals();
    $loan->set_value("dateToBeReturned", $whenToReturn);

    // if admin/manager then check to see if an alternate user was selected
    if (isset($userID) && $userID != "" && (in_array("admin", $permissions) || in_array("manage", $permissions))) {
      if ($userID != $current_user->get_id()) {
        $person = new person;
        $person->set_id($userID);
        $person->select();

        if ($person->get_value("emailAddress") != "") {
          $to = sprintf("%s %s <%s>", $person->get_value("firstName"), $person->get_value("surname"), $person->get_value("emailAddress"));
          $subject = "Item Loan";

          $person = new person;
          $person->set_id($current_user->get_id());
          $person->select();

          $message.= "admin/manager: \"".$person->get_value("username")."\" "."has borrowed item: \"".$item->get_value("itemName")."\" for you\n";

          if ($person->get_value("emailAddress") != "") {
            $from = "From: Alloc Loans <".$person->get_value("emailAddress").">";
          } else {
            $from = "From: Alloc Loans <alloc-admin@cyber.com.au>";
          }
          // email userID saying that admin/manager: $current_user->get_id() has borrowed item for them
          mail($to, $subject, $message, $from);
        }
      }
      $loan->set_value("personID", $userID);
    } else {
      $loan->set_value("personID", $current_user->get_id());
    }

    $loan->set_value("dateBorrowed", $today);
    $loan->set_value("dateReturned", "0000-00-00");
    $loan->save();

    header("location:".$TPL["url_alloc_loanAndReturn"]);
  }
}



if ($returnItem) {

  $dbTemp = new db_alloc;
  $dbTemp->query("select * from loan where itemID=$itemID and dateReturned='0000-00-00'");

  $db->query("select * from loan where loan.itemID=$itemID and dateBorrowed>dateReturned");
  $db->next_record();
  $loan->read_db_record($db);
  $loan->set_id($db->f("loanID"));
  $loan->select();
  $loan->set_value("dateReturned", $today);

  // check to see if admin/manager returning someone elses item, and sent email
  if ($loan->get_value("personID") != $current_user->get_id()) {
    if (in_array("admin", $permissions) || in_array("manager", $permissions)) {
      $person = new person;
      $person->set_id($loan->get_value("personID"));
      $person->select();

      if ($person->get_value("emailAddress") != "") {
        $to = sprintf("%s %s <%s>", $person->get_value("firstName"), $person->get_value("surname"), $person->get_value("emailAddress"));
        $subject = "Item Loan";

        $person = new person;
        $person->set_id($current_user->get_id());
        $person->select();

        $message.= "admin/manager: \"".$person->get_value("username")."\" "."has returned item: \"".$item->get_value("itemName")."\" for you\n";

        if ($person->get_value("emailAddress") != "") {
          $from = "From: Alloc Loans <".$person->get_value("emailAddress").">";
        } else {
          $from = "From: Alloc Loans <alloc-admin@cyber.com.au>";
        }
        // email userID saying that admin/manager: $current_user->get_id() has returned item for them
        mail($to, $subject, $message, $from);
      }
      $loan->save();
    }
    // if personID != $current_user->get_id() and not an admin/manager then shouldnt be able to return
  } else {
    $loan->save();
  }

  header("location:".$TPL["url_alloc_loanAndReturn"]);
}



if ($borrow) {
  include_template("templates/itemBorrowM.tpl");
} else if ($return) {
  include_template("templates/itemReturnM.tpl");
}


page_close();






?>
