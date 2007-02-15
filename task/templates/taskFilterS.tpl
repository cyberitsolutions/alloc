<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshProjectList(radiobutton) \{
  url = '{$url_alloc_updateProjectList}projectType='+radiobutton.value
  makeAjaxRequest(url,'updateProjectList',1)
\}

// Here's the callback function
function updateProjectList(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("projectListDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
\}
</script>


<form action="{$url_form_action}" method="post">
<table border="0" cellspacing="0" cellpadding="3" width="100%">
  <tr>
    <td align="center">

      <table class="filter" align="center">
        <tr>
          <td>{get_help("taskList_project_superset")}</td>
          <td><b>Project Superset</b></td>
          <td><b>Projects</b></td>
          <td><b>Task Type</b></td>
          <td><b>Task Status</b></td>
          <td><b>Assigned To</b></td> 
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td></td>
          <td valign="top" align="right" rowspan="2">
            <label for="pt_mine">My Projects</label><input type="radio" id="pt_mine" name="projectType" value="mine" onClick="refreshProjectList(this)"{$projectType_checked_mine}><br/>
            <label for="pt_pm"><nobr>My Project Managed</label><input type="radio" id="pt_pm" name="projectType" value="pm" onClick="refreshProjectList(this)"{$projectType_checked_pm}></nobr><br/>
            <label for="pt_tsm">My Time Sheet Rec.</label><input type="radio" id="pt_tsm" name="projectType" value="tsm" onClick="refreshProjectList(this)"{$projectType_checked_tsm}><br/>
            <label for="pt_curr">Current</label><input type="radio" id="pt_curr" name="projectType" value="curr" onClick="refreshProjectList(this)"{$projectType_checked_curr}><br/>
            <label for="pt_pote">Potential</label><input type="radio" id="pt_pote" name="projectType" value="pote" onClick="refreshProjectList(this)"{$projectType_checked_pote}><br/>
            <label for="pt_arch">Archived</label><input type="radio" id="pt_arch" name="projectType" value="arch" onClick="refreshProjectList(this)"{$projectType_checked_arch}><br/>
            <label for="pt_all">Everything</label><input type="radio" id="pt_all" name="projectType" value="all" onClick="refreshProjectList(this)"{$projectType_checked_all}><br/>
            &nbsp;
          </td>
          <td valign="top" rowspan="2"><div id="projectListDropdown">{$projectOptions}</div></td>
          <td valign="top" rowspan="2"><select name="taskTypeID[]" size="6" multiple="true">{$taskTypeOptions}</select></td>
          <td valign="top"><select name="taskStatus" size="1">{$taskStatusOptions}</select></td>
          <td valign="top"><select name="personID">{$personOptions}</select></td>  
        </tr>
        <tr>
          <td></td>
          <td colspan="3">
    
            <table class="filter" align="center" width="95%">
              <tr>
                <td valign="top"><b><nobr>Display Options</nobr></b></td>
                <td colspan="3" align="right">
                  <nobr>
List by Priority <input type="radio" name="taskView" value="prioritised"{$taskView_checked_prioritised}><br/>
List by Project <input type="radio" name="taskView" value="byProject"{$taskView_checked_byProject}>
                  </nobr>
                </td>
              </tr>
              <tr>
                <td align="right"><nobr>Desc &amp; Comments</nobr></td><td><input type="checkbox" name="showDescription"{$showDescription_checked}></td>
                <td align="right">Task Dates</td><td><input type="checkbox" name="showDates"{$showDates_checked}></td>
              </tr>
              <tr>
                <td align="right"><nobr>Task Creator</nobr></td><td><input type="checkbox" name="showCreator"{$showCreator_checked}></td>
                <td align="right"><nobr>Assigned To</nobr></td><td><input type="checkbox" name="showAssigned"{$showAssigned_checked}></td>
              </tr>
              <tr>
                <td align="right"><nobr>Est, Act &amp; Percent</nobr></td><td><input type="checkbox" name="showTimes"{$showTimes_checked}></td>
                <td align="right">Task ID </td><td><input type="checkbox" name="showTaskID"{$showTaskID_checked}></td>
              </tr>
              <tr>
                <td align="right">Priority Info</td><td><input type="checkbox" name="showPriority"{$showPriority_checked}></td>
                <td align="right">Task Status</td><td><input type="checkbox" name="showStatus"{$showStatus_checked}></td>
              </tr>
                
            </table>

          </td>
          <td valign="bottom" align="right"><input type="submit" name="applyFilter" value="Filter"> {get_help("taskList_filter")}</td>
        </tr>

      </table>

    </td>
  </tr>
</table>
</form>
