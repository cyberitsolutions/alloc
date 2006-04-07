<?php
require_once("alloc.inc");

function show_days($template_name) {
  global $date_to_view, $TPL;

  for ($day_offset = 0; $day_offset < 7; $day_offset++) {
    $TPL["timesheet_date"] = date("Y-m-d (D)", $date_to_view);
    $TPL["daily_hours_total"] = 0;
    include_template($template_name);
    $date_to_view = mktime(0, 0, 0, date("m", $date_to_view), date("d", $date_to_view) + 1, date("Y", $date_to_view));
  }
}

function show_timeSheetItems($template_name) {
  global $date_to_view, $current_user, $TPL;
  $query = sprintf("SELECT * 
                      FROM timeSheetItem 
                           LEFT JOIN timeSheet ON timeSheetItem.timeSheetID = timeSheet.timeSheetID
                           LEFT JOIN project ON timeSheet.projectID = project.projectID
                      WHERE dateTimeSheetItem='%s'
                            AND timeSheet.personID=%d", date("Y-m-d", $date_to_view), $current_user->get_id());
  $db = new db_alloc;
  $db->query($query);
  while ($db->next_record()) {
    $timeSheetItem = new timeSheetItem;
    $timeSheetItem->read_db_record($db);
    $timeSheetItem->set_tpl_values();
    if ($timeSheetItem->get_value("unit") == "Hour") {
      $TPL["daily_hours_total"] += $timeSheetItem->get_value("timeSheetItemDuration");
    }

    $project = new project;
    $project->read_db_record($db);
    $project->set_tpl_values();
    if ($project->get_value("projectShortName")) {
      $TPL["item_description"] = $project->get_value("projectShortName");
    } else {
      $TPL["item_description"] = $project->get_value("projectName");
    }

    include_template($template_name);
  }
}

if (isset($start_date)) {
  $date_to_view = $start_date;
} else {
  $date_to_view = mktime(0, 0, 0);
}

while (date("D", $date_to_view) != "Sun") {
  $date_to_view = mktime(0, 0, 0, date("m", $date_to_view), date("d", $date_to_view) - 1, date("Y", $date_to_view));
}

$TPL["timesheet_date"] = date("Y-m-d (D)", $date_to_view);
$prev_week = mktime(0, 0, 0, date("m", $date_to_view), date("d", $date_to_view) - 7, date("Y", $date_to_view));
$next_week = mktime(0, 0, 0, date("m", $date_to_view), date("d", $date_to_view) + 7, date("Y", $date_to_view));
$TPL["prev_week_url"] = $TPL["url_alloc_weeklyTime"]."start_date=$prev_week";
$TPL["next_week_url"] = $TPL["url_alloc_weeklyTime"]."start_date=$next_week";
include_template("templates/weeklyTimeM.tpl");

page_close();



?>
