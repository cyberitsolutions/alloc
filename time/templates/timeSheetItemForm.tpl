<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function refreshTaskList(radiobutton) \{
  url = '{$url_alloc_updateTimeSheetTaskList}task_type='+radiobutton.value+'&timeSheetID={$timeSheet_timeSheetID}&taskID={$taskListDropdown_taskID}'
  makeAjaxRequest(url,'taskListDropdown')
\}
</script>

<form action="{$url_alloc_timeSheet}" method="post">
{$table_box}
  <tr>
    <th colspan="5">Create Time Sheet Item</th>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td colspan="4">

      <table cellpadding="0" cellspacing="2" border="0">
        <tr>
          <td valign="bottom">Date</td>
          <td valign="bottom">Duration</td>
        </tr>
   
        <tr>
          <td>
            {get_calendar("timeSheetItem_dateTimeSheetItem",$TPL["timeSheetItem_dateTimeSheetItem"])}
          </td>
          <td>
            <input type="text" size="5" name="timeSheetItem_timeSheetItemDuration" value="{$timeSheetItem_timeSheetItemDuration}">
            <select name="timeSheetItem_timeSheetItemDurationUnitID">{$timeSheetItem_unit_options}</select>
            &nbsp;at&nbsp;$<input type="text" size="7" name="timeSheetItem_rate" value="{$timeSheetItem_rate}"> (inc. {$taxName})&nbsp;&times;&nbsp;<select name="timeSheetItem_multiplier">{$timeSheetItem_multiplier_options}</select>
          </td>
        </tr>
        <tr>
          <td valign="bottom" colspan="2"><a href="{$url_alloc_task}projectID={$projectID}&timeSheetID={$timeSheet_timeSheetID}">New Task</a></td>
          <td valign="bottom" rowspan="2"> 
            <label for="task_type_open">Open Tasks</label><input id="task_type_open" type="radio" name="task_type" value="open" onClick="refreshTaskList(this)" checked>
            <label for="task_type_recent_closed">Recently Closed</label><input id="task_type_recent_closed" type="radio" name="task_type" value="recent_closed" onClick="refreshTaskList(this)">
            <label for="task_type_all">All Tasks</label><input id="task_type_all" type="radio" name="task_type" value="all" onClick="refreshTaskList(this)">
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
    <td valign="top"></td>
    <td colspan="3" valign="top">
      Comments<br>
      {get_textarea("timeSheetItem_comment",$TPL["timeSheetItem_comment"])}
      Private Comment <input type="checkbox" name="timeSheetItem_commentPrivate"{$commentPrivateChecked}>
      </div>
    </td>
    <td colspan="1" valign="top" align="right"><br/>{$timeSheetItem_buttons}</td>
  </tr>
</table>


<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetItem_timeSheetID}">
<input type="hidden" name="timeSheetItem_personID" value="{$timeSheetItem_personID}">
</form>

