<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshTaskList(radiobutton) \{
  url = '{$url_alloc_updateTimeSheetTaskList}task_type='+radiobutton.value+'&timeSheetID={$timeSheet_timeSheetID}&taskID={$taskListDropdown_taskID}'
  makeAjaxRequest(url,'updateTimeSheetTaskList',1)
\}

// Here's the callback function
function updateTimeSheetTaskList(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("taskListDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
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
            <input type="text" size="11" name="timeSheetItem_dateTimeSheetItem" value="{$timeSheetItem_dateTimeSheetItem}"><input type="button" value="Today" onClick="timeSheetItem_dateTimeSheetItem.value='{$today}'">
          </td>
          <td>
            <input type="text" size="5" name="timeSheetItem_timeSheetItemDuration" value="{$timeSheetItem_timeSheetItemDuration}">
            <select name="timeSheetItem_timeSheetItemDurationUnitID">{$timeSheetItem_unit_options}</select>
            &nbsp;x&nbsp;$<input type="text" size="7" name="timeSheetItem_rate" value="{$timeSheetItem_rate}">
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
            <div id="taskListDropdown">
              {$taskListDropdown}
            </div>
          </td>
        </tr>
      </table>

    </td>
  </tr>
  <tr>
    <td valign="top">
          <br/>
          <div id="shrink_tsi_note" style="display:none;">
            <img src="../images/shrink.gif"
                   onMouseUp="document.getElementById('tsi_note').style.height='22px';
                              document.getElementById('shrink_tsi_note').style.display='none'
                              document.getElementById('grow_tsi_note').style.display='inline'">
          </div>
          <div id="grow_tsi_note">
            <img src="../images/grow.gif"
                   onMouseUp="document.getElementById('tsi_note').style.height='150px';
                              document.getElementById('grow_tsi_note').style.display='none'
                              document.getElementById('shrink_tsi_note').style.display='inline'">
          </div>
    </td>
    <td colspan="3">
      Task Comments<br/>
      <textarea rows="3" cols="70" name="timeSheetItem_comment" id="tsi_note" style="height:22px;"
                onFocus="document.getElementById('tsi_note').style.height='150px';
                         document.getElementById('grow_tsi_note').style.display='none'
                         document.getElementById('shrink_tsi_note').style.display='inline'">{$timeSheetItem_comment}</textarea>
      Private Comment <input type="checkbox" name="timeSheetItem_commentPrivate"{$commentPrivateChecked}>
    </td>
    <td colspan="1" valign="top" align="right"><br/>{$timeSheetItem_buttons}</td>
  </tr>
</table>


<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetItem_timeSheetID}">
<input type="hidden" name="timeSheetItem_personID" value="{$timeSheetItem_personID}">
</form>

