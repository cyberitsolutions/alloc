<?php


// WARNING: You may need to run this patch a couple of times!
// I.e. it may error out, but just go back and apply it again.

// This patch contains lots of stuff that fixes up missing data. The
// stuff that's wrapped in the if (config::for_cyber()) are Cybersource
// specific data fixes. The rest of the patch is ok for everyone elses data.

$db = new db_alloc();
$db2 = new db_alloc();
$db3 = new db_alloc();

function debug_echo($str="") {
  #$str and print $str;
}


if (config::for_cyber()) {

  // This linked up old tfs with people who had been deleted from cybersources database.
  // select distinct tfPerson.personID, tfName, person.personID from tfPerson
  // left join person on person.personID = tfPerson.personID left join tf on tf.tfID
  // = tfPerson.tfID where person.personID is null;

  // 201 206 207 209 ?
  $people[1] = array("conzdefunct" ,"Con","Zymaris (not in use)");
  $people[17] = array("mitch"      ,"Mitch","Davis");
  $people[18] = array("ldm"        ,"Lachlan","Mulcahy");
  $people[19] = array("asn"        ,"Andrew","Noble");
  $people[37] = array("djr"        ,"Darryl","Ross");
  $people[42] = array("zoltan"     ,"Zoltan","Olah");
  $people[48] = array("tim"        ,"","");
  $people[52] = array("pkl"        ,"","");
  $people[53] = array("ashridah"   ,"Andrew","Pilley");
  $people[59] = array("jaws"       ,"Jen","Shaw");
  $people[65] = array("sharkey"    ,"Nick","Moore");
  $people[66] = array("alvin"      ,"Alvin","");
  $people[71] = array("army"       ,"Paul","Armstronf");
  $people[73] = array("robc"       ,"Rob","Chalmers");
  $people[74] = array("fry"        ,"Simon","Freiberg");
  $people[75] = array("jsg"        ,"Jonathan","Gray");
  $people[78] = array("mikec"      ,"Mike","Ciaverella");
  $people[79] = array("aeh"        ,"Andrew","Harris");
  $people[83] = array("rdaly"      ,"Richella","Daly");
  $people[85] = array("karl"       ,"Karl","Martindale-Vale");
  $people[86] = array("jens"       ,"Jens","Porup");
  $people[93] = array("gv"         ,"George","Vlahoulis");
  $people[95] = array("anshul"     ,"Anshul","Gupta");
  $people[96] = array("mattp"      ,"");
  $people[98] = array("likic"      ,"");
  $people[99] = array("Sean"       ,"Sean","Hynes");
  $people[106] = array("ojw"       ,"","");
  $people[108] = array("benm"      ,"Ben","McGinnes");
  $people[110] = array("tony"      ,"Tony","Wood");
  $people[117] = array("achalmers" ,"Andrew","Chalmers");
  $people[118] = array("adrian"    ,"Adrian","Close");
  $people[125] = array("cmeo"      ,"","");
  $people[128] = array("enno"      ,"","");
  $people[136] = array("matt"      ,"","");
  $people[138] = array("pauld"     ,"","");
  $people[143] = array("pbd"       ,"Paul","Dwerryhouse");
  $people[148] = array("anthonya"  ,"Anthony","Agius");

  foreach ($people as $personID=>$info) {
    $q = prepare("select * from person where personID = %d",$personID);
    $db->query($q);
    if (!$db->row()) {
      debug_echo("<br>Cyber only: Inserted new person: ".$personID." ".$info[0]." ".$info[1]." ".$info[2]);
      $q = prepare("INSERT INTO person (personID,personActive,username,firstName,surname)
                          VALUES (%d,0,'%s','%s','%s')",$personID,$info[0],$info[1],$info[2]);
      #echo $q;
      $db2->query($q);
    }
  }

  // spare record
  $q = "delete from clientContact where clientContactID = 434";
  $db->query($q);
  $q = "delete from loan where loanID = 23";
  $db->query($q);


  $q = "select * from client where clientName = 'Cybersource Placeholder for Invoices that have no Client'";
  $db->query($q);
  if (!($row = $db->row())){
    $q = "insert into client (clientName, clientStatus) values ('Cybersource Placeholder for Invoices that have no Client','current')";
    $db->query($q);
    $q = "select * from client where clientName = 'Cybersource Placeholder for Invoices that have no Client'";
    $db->query($q);
    $row = $db->row();
  }
  $default_clientID = $row["clientID"];

  $q = prepare("update invoice set clientID = %d WHERE clientID = 0",$default_clientID);
  $db->query($q);

  $q = "update timeSheet set personID = 155 where recipient_tfID = 104 and personID = 0";
  $db->query($q);

}

$q = "select * from tf where tfComments = 'This tf represents the source tf for transactions that were created before alloc started using double-entry transactions.'";
$db->query($q);
if (!($row = $db->row())){
  $q = "insert into tf (tfName, tfComments,tfActive) values ('none','This tf represents the source tf for transactions that were created before alloc started using double-entry transactions.',0)";
  $db->query($q);
  $q = "select * from tf where tfComments = 'This tf represents the source tf for transactions that were created before alloc started using double-entry transactions.'";
  $db->query($q);
  $row = $db->row();
}

$tfID = $row["tfID"];

$q = prepare("update transaction set fromTfID = %d where fromTfID = 0",$tfID);
$db->query($q);
$q = prepare("update transactionRepeat set fromTfID = %d where fromTfID = 0",$tfID);
$db->query($q);
$q = prepare("update productCost set fromTfID = %d where fromTfID = 0",$tfID);
$db->query($q);
$q = "update person set personModifiedUser = null where personModifiedUser = 0";
$db->query($q);
$q = "delete from sess";
$db->query($q);
$q = "update task set parentTaskID = null where parentTaskID = 0";
$db->query($q);
$q = prepare("update productCost set fromTfID = NULL where fromTfID = 0");
$db->query($q);
$q = prepare("update productCost set tfID = NULL where tfID = 0");
$db->query($q);

$q = prepare("select * from timeSheetItem where personID = 0");
$db->query($q);
while($row = $db->row()) {
  if ($row["timeSheetID"]) {
    $q = prepare("select personID from timeSheet where timeSheetID = %d",$row["timeSheetID"]);
    $db2->query($q);
    if ($r = $db2->row()) {
      $q = prepare("update timeSheetItem set personID = %d where timeSheetItemID = %d",$r["personID"],$row["timeSheetItemID"]);
      $db3->query($q);
    }
  }
}
fix_fk("productCost.fromTfID", "tf.tfID");
fix_fk("transactionRepeat.transactionRepeatCreatedUser", "person.personID");
fix_fk("transactionRepeat.transactionRepeatModifiedUser", "person.personID");
fix_fk("transactionRepeat.fromTfID", "tf.tfID");
fix_fk("transaction.invoiceItemID", "invoiceItem.invoiceItemID");
fix_fk("transaction.transactionModifiedUser", "person.personID");
fix_fk("transaction.transactionCreatedUser", "person.personID");
fix_fk("transaction.projectID", "project.projectID");
fix_fk("transaction.fromTfID", "tf.tfID");
fix_fk("transaction.expenseFormID", "expenseForm.expenseFormID");
fix_fk("timeSheetItem.taskID", "task.taskID");
fix_fk("timeSheetItem.personID", "person.personID");
fix_fk("timeSheetItem.timeSheetID", "timeSheet.timeSheetID");
fix_fk("timeSheet.recipient_tfID", "tf.tfID");
fix_fk("timeSheet.personID", "person.personID");
fix_fk("timeSheet.projectID", "project.projectID");
fix_fk("tfPerson.personID", "person.personID");
fix_fk("tfPerson.tfID", "tf.tfID");
fix_fk("interestedParty.clientContactID", "clientContact.clientContactID");
//fix_fk("task.parentTaskID", "task.taskID");
fix_fk("task.projectID", "project.projectID");
fix_fk("task.taskModifiedUser", "person.personID");
fix_fk("task.managerID", "person.personID");
fix_fk("task.personID", "person.personID");
fix_fk("task.creatorID", "person.personID");
fix_fk("proficiency.personID", "person.personID");
//fix_fk("sess.personID", "person.personID");
fix_fk("sentEmailLog.sentEmailLogCreatedUser", "person.personID");
fix_fk("reminder.reminderModifiedUser", "person.personID");
fix_fk("projectPerson.projectPersonModifiedUser", "person.personID");
fix_fk("projectPerson.roleID", "role.roleID");
fix_fk("projectPerson.personID", "person.personID");
fix_fk("projectPerson.projectID", "project.projectID");
fix_fk("projectCommissionPerson.tfID", "tf.tfID");
fix_fk("projectCommissionPerson.personID", "person.personID");
fix_fk("projectCommissionPerson.projectID", "project.projectID");
fix_fk("project.projectModifiedUser", "person.personID");
fix_fk("project.clientID", "client.clientID");
fix_fk("project.clientContactID", "clientContact.clientContactID");
fix_fk("permission.personID", "person.personID");
fix_fk("loan.loanModifiedUser", "person.personID");
fix_fk("loan.personID", "person.personID");
fix_fk("item.personID", "person.personID");
fix_fk("item.itemModifiedUser", "person.personID");
fix_fk("invoiceItem.transactionID", "transaction.transactionID");
fix_fk("invoiceItem.expenseFormID", "expenseForm.expenseFormID");
fix_fk("invoiceItem.timeSheetItemID", "timeSheetItem.timeSheetItemID");
fix_fk("invoiceItem.timeSheetID", "timeSheet.timeSheetID");
fix_fk("invoice.clientID", "client.clientID");
fix_fk("client.clientModifiedUser", "person.personID");
fix_fk("clientContact.clientID", "client.clientID"); // may need to change this to look for clientCOntacts including ones that are null
fix_fk("comment.commentCreatedUser", "person.personID");
fix_fk("comment.commentCreatedUserClientContactID", "clientContact.clientContactID");
fix_fk("expenseForm.clientID", "client.clientID");
fix_fk("expenseForm.expenseFormModifiedUser", "person.personID");
fix_fk("expenseForm.expenseFormCreatedUser", "person.personID");
fix_fk("expenseForm.transactionRepeatID", "transactionRepeat.transactionRepeatID");
fix_fk("transaction.productSaleID", "productSale.productSaleID");
fix_fk("transaction.productSaleItemID", "productSaleItem.productSaleItemID");
fix_fk("productCost.tfID", "tf.tfID");
fix_fk("productCost.fromTfID", "tf.tfID");
fix_fk("productSaleItem.productID", "product.productID");
fix_fk("productSaleItem.productSaleID", "productSale.productSaleID");

function fix_fk ($c,$p) {
  list($child,$child_fk) = explode(".",$c);
  list($parent,$parent_fk) = explode(".",$p);

  $child_pk = $child.".".$child."ID";
  $parent_pk = $parent.".".$parent."ID";

  $q = "select ".$child_pk.",".$c."
          from ".$child."
     left join ".$parent." on ".$c." = ".$p."
         where ".$c." is not null and ".$p." is null;";
  ###echo "<br><hr>".$q;

  $db = new db_alloc();
  $db2 = new db_alloc();
  $db->query($q);
  while ($row = $db->row()) {
    $q = prepare("UPDATE ".$child." SET ".$c." = NULL WHERE ".$child_pk." = ".$row[$child."ID"]);
    debug_echo("<br>&nbsp;&nbsp;&nbsp;".$q);
    $db2->query($q);
  }

}


if (config::for_cyber()) {
  $q = "update task set creatorID = 1 where creatorID = 0";
  $db->query($q);

  $q = "update timeSheetItem set personID = 1 where personID = 0";
  $db->query($q);

  $q = "update timeSheet set personID = 1 where personID = 0";
  $db->query($q);

  $q = "update timeSheet set projectID = 46 where timeSheetID = 1";
  $db->query($q);
}

// bad records
$q = "delete from timeSheetItem where timeSheetID = 0";
$db->query($q);

// bad records
$q = "delete from projectPerson where projectID = 0 or personID = 0";
$db->query($q);
$q = "update projectPerson set roleID = 2 where roleID = 0";
$db->query($q);
$q = "update task set creatorID = personID where creatorID = 0 and personID is not null";
$db->query($q);

$q = "delete from tfPerson where tfID = 0 or personID = 0";
$db->query($q);
$q = "delete from projectCommissionPerson where tfID = 0 or projectID = 0";
$db->query($q);


$q = "update absence set absenceType = null where absenceType = ''";
$db->query($q);

$q = "update invoice set invoiceStatus = 'edit' where invoiceStatus = ''";
$db->query($q);

$q = "update item set itemType = 'cd' where itemType = ''";
$db->query($q);

$q = "update sentEmailLog set sentEmailType = 'timeSheet_comments' where sentEmailType = ''";
$db->query($q);

$q = "delete from proficiency where personID = 0";
$db->query($q);



?>
