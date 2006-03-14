<?php
class timeSheetListHomeItem extends home_item {
  function timeSheetListHomeItem() {
    global $auth, $TPL;
    home_item::home_item("time_list", "Time Sheets", "time", "timeSheetListH.tpl", "narrow");

    // Get averages for hours worked over the past fortnight and year
    $t = new timeSheetItem;
    list($hours_sum,$dollars_sum) = $t->get_averages(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y"))),$auth->auth["uid"]);
    list($hours_avg,$dollars_avg) = $t->get_fortnightly_average($auth->auth["uid"]);
    $TPL["hours_sum"] = sprintf("%d",$hours_sum[$auth->auth["uid"]]);
    $TPL["hours_avg"] = sprintf("%d",$hours_avg[$auth->auth["uid"]]);
    $TPL["dollars_sum"] = sprintf("%d",$dollars_sum[$auth->auth["uid"]]);
    $TPL["dollars_avg"] = sprintf("%d",$dollars_avg[$auth->auth["uid"]]);
  }

  function show_time_sheets($template_name) {
    global $current_user, $TPL;

    $query = sprintf("SELECT timeSheet.*, sum(timeSheetItem.timeSheetItemDuration * timeSheetItem.rate) as total_dollars
                        FROM timeSheet
                        LEFT JOIN timeSheetItem on timeSheet.timeSheetID = timeSheetItem.timeSheetID
                      WHERE timeSheet.personID=%d 
                         AND timeSheet.status != 'invoiced' 
                    GROUP BY timeSheet.timeSheetID
                    ORDER BY timeSheet.status", $current_user->get_id());
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $timeSheet = new timeSheet;
      $timeSheet->read_db_record($db);
      $timeSheet->set_tpl_values();

      if ($timeSheet->get_value("status") == "edit") {
        $TPL["status"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheet->get_id()."\">".$timeSheet->get_value("status")."</a>";
      } else {
        $TPL["status"] = $timeSheet->get_value("status");
      }

      $TPL["total_dollars"] = "\$0";
      $db->f("total_dollars") > 0 and $TPL["total_dollars"] = "\$".sprintf("%d", $db->f("total_dollars"));

      $project = $timeSheet->get_foreign_object("project");
      if ($project->get_value("projectShortName")) {
        $TPL["projectName"] = $project->get_value("projectShortName");
      } else {
        $TPL["projectName"] = $project->get_value("projectName");
      }

      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
