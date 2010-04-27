{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th colspan="2">Weekly Timesheet View</th>
    <th class="right" colspan="5">
      <a href="{$url_alloc_timeSheetList}">Default Time View</a>
      <a href="{$url_alloc_timeSheet}userID={$userID}">New Time Sheet</a>
    </th>
  </tr>
  <tr>
    <th class="center" colspan="7"><a href="{$prev_week_url}"> <- Previous Week</a>&nbsp;<a href="{$next_week_url}">Next Week -> </a></th>
  </tr>
    <tr>
      {show_days("templates/weeklyTimeDayR.tpl")}
    </tr>
  </table>
{page::footer()}
