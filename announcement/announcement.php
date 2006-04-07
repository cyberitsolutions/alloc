<?php
// initialise the request
require_once("alloc.inc");

  // create an object to hold an announcement
$announcement = new announcement;

  // load the announcement from the database
if (isset($announcementID)) {
  $announcement->set_id($announcementID);
  $announcement->select();
}
  // read announcement variables set by the request
$announcement->read_globals();

  // process submission of the form using the save button
if (isset($save)) {
  $announcement->set_value("personID", $current_user->get_id());
  $announcement->save();

  // process submission of the form using the delete button
} else if (isset($delete)) {
  $announcement->delete();
  page_close();
  header("Location: ".$TPL["url_alloc_announcementList"]);
  exit();
}
  // load data for display in the template
$announcement->set_tpl_values();

  // invoke the page's main template
include_template("templates/announcementM.tpl");


  // Close the request
page_close();



?>
