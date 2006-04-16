{:show_header}
{:show_toolbar}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshProjectList(radiobutton) {
  url = '{url_alloc_updateProjectList}projectType='+radiobutton.value
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
          <td align="center">

            <table class="filter" align="center">
              <tr>
                <td>&nbsp;</td>
                <td>Project</td>
                <td>Task Type</td>
                <td>Task Status</td>
                <td>Assigned To</td> 
                <td>&nbsp;</td>
                <!-- <td rowspan="2">{:help_button taskSummaryFilter}</td> -->
                <td>&nbsp;</td>
              </tr>

              <tr>
                <td valign="bottom" align="right" rowspan="2">
                  My Projects <input type="radio" name="projectType" value="mine" onClick="refreshProjectList(this)"{projectType_checked_mine}><br/>
                  <nobr>My Project Managed <input type="radio" name="projectType" value="pm" onClick="refreshProjectList(this)"{projectType_checked_pm}></nobr><br/>
                  My Time Sheet Rec. <input type="radio" name="projectType" value="tsm" onClick="refreshProjectList(this)"{projectType_checked_tsm}><br/>
                  Current <input type="radio" name="projectType" value="curr" onClick="refreshProjectList(this)"{projectType_checked_curr}><br/>
                  Potential <input type="radio" name="projectType" value="pote" onClick="refreshProjectList(this)"{projectType_checked_pote}><br/>
                  Archived <input type="radio" name="projectType" value="arch" onClick="refreshProjectList(this)"{projectType_checked_arch}><br/>
                  All Projects <input type="radio" name="projectType" value="all" onClick="refreshProjectList(this)"{projectType_checked_all}><br/>
                  &nbsp;
                </td>
                <td valign="top" rowspan="2"><div id="projectListDropdown">{projectOptions}</div></td>
                <td valign="top" rowspan="2"><select name="taskTypeID[]" size="6" multiple="true">{taskTypeOptions}</select></td>
                <td valign="top"><select name="taskStatus" size="1">{taskStatusOptions}</select></td>
                <td valign="top"><select name="personID">{personOptions}</select></td>  
                <td valign="top" colspan="2"><input type="checkbox" name="personIDonly"{personIDonly_checked}>Only</td>
              </tr>
              <tr>
                <td colspan="3">
          
                  <table class="filter" align="center" width="95%">
                    <tr>
                      <td><b>Display</b></td>
                      <td colspan="3" align="right">
                        <nobr>
                          View By
                          Priority <input type="radio" name="taskView" value="prioritised"{taskView_checked_prioritised}>
                          Project <input type="radio" name="taskView" value="byProject"{taskView_checked_byProject}> 
                        </nobr>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">Description</td><td><input type="checkbox" name="showDescription"{showDescription_checked}></td>
                      <td align="right">Task Dates</td><td><input type="checkbox" name="showDates"{showDates_checked}></td>
                    </tr>
                    <tr>
                      <td align="right">Task Creator</td><td><input type="checkbox" name="showCreator"{showCreator_checked}></td>
                      <td align="right">Assigned To</td><td><input type="checkbox" name="showAssigned"{showAssigned_checked}></td>
                    </tr>
                    <tr>
                      <td align="right"><nobr>Estimate/Actual</nobr></td><td><input type="checkbox" name="showTimes"{showTimes_checked}></td>
                      <td align="right">% Complete</td><td><input type="checkbox" name="showPercent"{showPercent_checked}></td>
                    </tr>
                    <tr>
                      <td align="right">Priority Info</td><td><input type="checkbox" name="showPriority"{showPriority_checked}></td>
                      <td align="right">Task Status</td><td><input type="checkbox" name="showStatus"{showStatus_checked}></td>
                    </tr>
                      
                  </table>

                </td>
                <td valign="bottom" align="right"><input type="submit" name="applyFilter" value="Filter"></td>
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
      {task_summary}
    </td>
  </tr>
</table>
{:show_footer}
