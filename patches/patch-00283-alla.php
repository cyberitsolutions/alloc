<?php


$q = prepare("SELECT * FROM person");
$db = new db_alloc();
$db->query($q);

while ($row = $db->row()) {
  $current_user = new person();
  $current_user->load_current_user($row["personID"]);

  if (sprintf("%d",$current_user->prefs["tasksGraphPlotHome"]) > 0) {
    $current_user->prefs["showCalendarHome"] = 1;
  }

  if (sprintf("%d",$current_user->prefs["projectListNum"]) > 0) {
    $current_user->prefs["showProjectHome"] = 1;
  }

  if ($current_user->prefs["receiveOwnTaskComments"] == 'no') {
    $current_user->prefs["receiveOwnTaskComments"] = 0;
  } else {
    $current_user->prefs["receiveOwnTaskComments"] = 1;
  }

  if ($current_user->prefs["showFilters"] == "no") {
    $current_user->prefs["showFilters"] = 0;
  } else {
    $current_user->prefs["showFilters"] = 1;
  }

  if ($current_user->prefs["dailyTaskEmail"] != "yes") {
    $current_user->prefs["dailyTaskEmail"] = 0;
  } else {
    $current_user->prefs["dailyTaskEmail"] = 1;
  }

  if ($current_user->prefs["topTasksStatus"]) {
    $current_user->prefs["showTaskListHome"] = 1;
    $current_user->prefs["taskListHome_filter"]["applyFilter"] = 1;
    $current_user->prefs["taskListHome_filter"]["personID"] = $current_user->get_id();
    $current_user->prefs["taskListHome_filter"]["taskStatus"] = $current_user->prefs["topTasksStatus"];
    unset($current_user->prefs["topTasksStatus"]);
  }

  if ($current_user->prefs["topTasksNum"]) {
    $current_user->prefs["showTaskListHome"] = 1;
    $current_user->prefs["taskListHome_filter"]["applyFilter"] = 1;
    $current_user->prefs["taskListHome_filter"]["personID"] = $current_user->get_id();
    $current_user->prefs["taskListHome_filter"]["limit"] = $current_user->prefs["topTasksNum"];
    unset($current_user->prefs["topTasksNum"]);
  }

  if ($current_user->prefs["showTimeSheetStats"]) {
    $current_user->prefs["showTimeSheetStatsHome"] = 1;
  } else {
    $current_user->prefs["showTimeSheetStatsHome"] = 0;
  }
  unset($current_user->prefs["showTimeSheetStats"]);

  if ($current_user->prefs["showNewTimeSheetItem"]) {
    $current_user->prefs["showTimeSheetItemHome"] = 1;
  } else {
    $current_user->prefs["showTimeSheetItemHome"] = 0;
  }
  unset($current_user->prefs["showNewTimeSheetItem"]);

  if ($current_user->prefs["showNewTsiHintItem"]) {
    $current_user->prefs["showTimeSheetItemHintHome"] = 1;
  } else {
    $current_user->prefs["showTimeSheetItemHintHome"] = 0;
  }
  unset($current_user->prefs["showNewTsiHintItem"]);

  $current_user->store_prefs();
}



?>
