<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function refreshTaskList(radiobutton) {
  url = '{$url_alloc_updateTimeSheetTaskList}task_type='+radiobutton.value+'&timeSheetID={$timeSheet_timeSheetID}&taskID={$taskListDropdown_taskID}'
  makeAjaxRequest(url,'taskListDropdown')
}
</script>

<form action="{$url_alloc_timeSheetItem}" method="post">
<table class="box">
  <tr>
    <th colspan="4">Create Time Sheet Item</th>
  </tr>
  <tr>
    <td colspan="4">

      <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td valign="bottom">Date</td>
          <td valign="bottom">Duration</td>
        </tr>
        <tr>
          <td>{page::calendar("timeSheetItem_dateTimeSheetItem",$tsi_dateTimeSheetItem)}</td>
          <td>
            <input type="text" size="5" name="timeSheetItem_timeSheetItemDuration" value="{$tsi_timeSheetItemDuration}">
            {if $tsi_unit_label}
              {$tsi_unit_label}
            {else}
              <select name="timeSheetItem_timeSheetItemDurationUnitID">{$tsi_unit_options}</select>
            {/}
            @
            {if $ts_rate_editable}
            {$currency} <input type="text" size="7" name="timeSheetItem_rate" value="{$tsi_rate}">
            {else}
              <input type="hidden" name="timeSheetItem_rate" value="{$tsi_rate}">{$currency}{$tsi_rate}
            {/}
&nbsp;&times;&nbsp;<select name="timeSheetItem_multiplier">{$tsi_multiplier_options}</select>
          </td>
        </tr>
        <tr>
          <td valign="bottom" colspan="2"><a tabindex="100" href="{$url_alloc_task}projectID={$projectID}&timeSheetID={$timeSheet_timeSheetID}">New Task</a></td>
          <td valign="bottom" rowspan="2"> 
            <label for="task_type_mine">My Tasks</label><input id="task_type_mine" type="radio" name="task_type" tabindex="100" value="mine" onClick="refreshTaskList(this)" checked>
            <label for="task_type_open">Open Tasks</label><input id="task_type_open" type="radio" name="task_type" tabindex="100" value="open" onClick="refreshTaskList(this)">
            <label for="task_type_recent_closed">Recently Closed</label><input id="task_type_recent_closed" type="radio" name="task_type" tabindex="100" value="recent_closed" onClick="refreshTaskList(this)">
            <label for="task_type_all">All Tasks</label><input id="task_type_all" type="radio" name="task_type" tabindex="100" value="all" onClick="refreshTaskList(this)">
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <div id="taskListDropdown" style="width:400px;">
              {$taskListDropdown}
            </div>
          </td>
        </tr>
      </table>

    </td>
  </tr>
  <tr>
    <td colspan="3" valign="top">
    Comments<br>
    {page::textarea("timeSheetItem_comment",$tsi_comment)}
    Private Comment <input type="checkbox" value="1" name="timeSheetItem_commentPrivate"{$commentPrivateChecked}>
    </td>
    <td colspan="1" valign="top" align="right"><br>{$tsi_buttons}</td>
  </tr>
</table>


<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$tsi_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$tsi_timeSheetID}">
<input type="hidden" name="timeSheetItem_personID" value="{$tsi_personID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>

