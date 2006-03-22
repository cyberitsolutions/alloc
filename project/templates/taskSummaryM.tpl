{:show_header}
{:show_toolbar}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshProjectList(radiobutton) {
  url = '{url_alloc_updateProjectList}&projectType='+radiobutton.value
  makeAjaxRequest(url,'updateProjectList',1)
}

// Here's the callback function
function updateProjectList(number) {
  if (http_request[number].readyState == 4) {
    if (http_request[number].status == 200) {
      document.getElementById("projectListDropdown").innerHTML = http_request[number].responseText;
    }
  }
}
</script>


{table_box}
  <tr>
    <th>Task Summary</th>
    <th class="right"><nobr><a href="{url_alloc_task}">New Task</a></nobr></th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{url_alloc_taskSummary}" method="post">
      <table border="0" cellspacing="0" cellpadding="3" width="100%">
        <tr>
          <td>

            <table class="filter" align="center">
              <tr>
                <td>&nbsp;</td>
                <td>Project</td>
                <td>Task Type</td>
                <td>Task Status</td>
                <td>Allocated To</td>
                <!-- <td rowspan="2">{:help_button taskSummaryFilter}</td> -->
                <td></td>
              </tr>
              <tr>
            
                <td valign="bottom" align="right">
                  My Projects <input type="radio" name="projectType" value="mine" onClick="refreshProjectList(this)"{projectType_checked_mine}><br/>
                  <nobr>My Project Managed <input type="radio" name="projectType" value="pm" onClick="refreshProjectList(this)"{projectType_checked_pm}></nobr><br/>
                  My Time Sheet Rec. <input type="radio" name="projectType" value="tsm" onClick="refreshProjectList(this)"{projectType_checked_tsm}><br/>
                  Current <input type="radio" name="projectType" value="curr" onClick="refreshProjectList(this)"{projectType_checked_curr}><br/>
                  All Projects <input type="radio" name="projectType" value="all" onClick="refreshProjectList(this)"{projectType_checked_all}>
                </td>
                <td valign="top"><div id="projectListDropdown">{projectOptions}</div></td>
                <td valign="top"><select name="taskTypeID[]" size="6" multiple="true">{taskTypeOptions}</select></td>
                <td valign="top"><select name="taskStatus" size="1">{taskStatusOptions}</select></td>
                <td valign="top"><select name="personID">{personOptions}</select>
                  <br/><select name="taskView" size="1">{taskViewOptions}</select>
                  <br/>Show Descriptions
                  <input type="checkbox" name="showDetails"{show_details_checked}>
                </td>
                <td valign="top"><input type="submit" name="applyFilter" value="Filter"></td>
              </tr>
            </table>

          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table border="0" cellspacing="0" cellpadding="3" width="100%">
      {task_summary}
      </table>
    </td>
  </tr>
</table>
{:show_footer}
