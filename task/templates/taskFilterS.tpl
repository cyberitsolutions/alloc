<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshProjectList(radiobutton) \{
  document.getElementById("projectListDropdown").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
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
          <td>&nbsp;</td>
          <td><b>{get_expand_link("project_superset","Projects ")}</b></td>
          <td><b>Task Status</b> {get_help("taskList_taskStatus")}</td>
          <td><b>Created By</b></td> 
          <td rowspan="6" valign="top">
          
            <table class="filter" align="center" width="95%">
              <tr>
                <td valign="top"><b>{get_expand_link("more_display_options","Display Options ")}</b></td>
              </tr>
              <tr>
                <td align="right">
                  <nobr>
<label for="list_prioritised">List by Priority</label> <input type="radio" id="list_prioritised" name="taskView" value="prioritised"{$taskView_checked_prioritised}><br/>
<label for="list_byProject">List by Project</label> <input type="radio" id="list_byProject" name="taskView" value="byProject"{$taskView_checked_byProject}>
                  </nobr>
                </td>
              </tr>
              <tr>
                <td>

                  <div id="more_display_options" style="display:none">
                  <table>
                    <tr>
                      <td align="right"><label for="showDescription" class="nobr">Desc &amp; Comments</label></td>
                      <td><input type="checkbox" id="showDescription" name="showDescription"{$showDescription_checked}></td>
                      <td align="right"><label for="showDates">Task Dates</label></td>
                      <td><input type="checkbox" id="showDates" name="showDates"{$showDates_checked}></td>
                    </tr>
                    <tr>
                      <td align="right"><label for="showCreator" class="nobr">Task Creator</label></td>
                      <td><input type="checkbox" id="showCreator" name="showCreator"{$showCreator_checked}></td>
                      <td align="right"><label for="showManager" class="nobr">Task Manager</label></td>
                      <td><input type="checkbox" id="showManager" name="showManager"{$showManager_checked}></td>
                    </tr>
                    <tr>
                      <td align="right"><label for="showTimes" class="nobr">Est, Act &amp; Percent</label></td>
                      <td><input type="checkbox" id="showTimes" name="showTimes"{$showTimes_checked}></td>
                      <td align="right"><label for="showAssigned" class="nobr">Assigned To</label></td>
                      <td><input type="checkbox" id="showAssigned" name="showAssigned"{$showAssigned_checked}></td>
                    </tr>
                    <tr>
                      <td align="right"><label for="showPriority" class="nobr">Priority Info</label></td>
                      <td><input type="checkbox" id="showPriority" name="showPriority"{$showPriority_checked}></td>
                      <td align="right"><label for="showStatus" class="nobr">Task Status</label></td>
                      <td><input type="checkbox" id="showStatus" name="showStatus"{$showStatus_checked}></td>
                    </tr>
                  </table>
                  </div>

                </td>
              </tr>
            </table>

          
          </td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td valign="top" align="right" rowspan="6">
            <div id="project_superset" style="display:none">
              <label for="pt_mine">My Projects</label><input type="radio" id="pt_mine" name="projectType" value="mine" onClick="refreshProjectList(this)"{$projectType_checked_mine}><br/>
              <label for="pt_pm"><nobr>My Project Managed</label><input type="radio" id="pt_pm" name="projectType" value="pm" onClick="refreshProjectList(this)"{$projectType_checked_pm}></nobr><br/>
              <label for="pt_tsm">My Time Sheet Rec.</label><input type="radio" id="pt_tsm" name="projectType" value="tsm" onClick="refreshProjectList(this)"{$projectType_checked_tsm}><br/>
              <label for="pt_curr">Current</label><input type="radio" id="pt_curr" name="projectType" value="curr" onClick="refreshProjectList(this)"{$projectType_checked_curr}><br/>
              <label for="pt_pote">Potential</label><input type="radio" id="pt_pote" name="projectType" value="pote" onClick="refreshProjectList(this)"{$projectType_checked_pote}><br/>
              <label for="pt_arch">Archived</label><input type="radio" id="pt_arch" name="projectType" value="arch" onClick="refreshProjectList(this)"{$projectType_checked_arch}><br/>
              <label for="pt_all">Everything</label><input type="radio" id="pt_all" name="projectType" value="all" onClick="refreshProjectList(this)"{$projectType_checked_all}><br/>
            </div>
            &nbsp;
          </td>
          <td valign="top" rowspan="6" style="width:275px"><div id="projectListDropdown">{$projectOptions}</div></td>
          <td valign="top"><select name="taskStatus" size="1">{$taskStatusOptions}</select></td>
          <td valign="top"><select name="creatorID">{$creatorPersonOptions}</select></td>  
        </tr>

        <tr>
          <td><b>Task Type</b></td>
          <td><b>Managed By</b></td>
        </tr>
        <tr>
          <td valign="top" rowspan="6"><select name="taskTypeID[]" size="6" multiple="true">{$taskTypeOptions}</select></td>
          <td><select name="managerID">{$managerPersonOptions}</select></td>
        </tr>

        <tr>
          <td><b>Assigned To</b></td>
        </tr>
        <tr>
          <td valign="top"><select name="personID">{$personOptions}</select></td>
        </tr>


        <tr>
          <td></td>
          <td valign="bottom" align="right"><input type="submit" name="applyFilter" value="Filter"></td><td valign="bottom">{get_help("taskList_filter")}</td>
        </tr>

      </table>

    </td>
  </tr>
</table>
</form>
