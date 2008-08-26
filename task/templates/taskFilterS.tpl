<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function refreshProjectList(radiobutton) \{
  url = '{$url_alloc_updateProjectList}projectType='+radiobutton.value;
  makeAjaxRequest(url, 'projectListDropdown')
\}
</script>

<form action="{$url_form_action}" method="post">
<table align="center" class="filter">
  <tr>
    <td>
      <div id="lf_div" style="display:inline;"> 
        Saved Filters
        <select name="savedViewID" onChange="if(this.value=='-1')\{$('#lf_div').slideToggle('slow');$('#fn_div').slideToggle('slow');\}">
          <option value=""></option>
          <option value="-1">Create a new saved filter...</option>
          {$savedViewOptions}
        </select>
        <input type="submit" name="loadFilter" value="Load" />
        <input type="submit" name="deleteFilter" value="Delete" class="delete_button" />
      </div>
      <div style="display:none;" id="fn_div">
        Filter Name
        <input type="text" size="18" name="filterName" value="" />
        <input type="submit" name="saveFilter" value="Save" />
        <input type="button" name="cancel" value="Cancel" onClick="$('#lf_div').slideToggle('fast');$('#fn_div').slideToggle('fast');" />
      </div>
    </td>
    <td>
      {get_help("taskList_savedFilter")}     
    </td>
  </tr>
</table>

<table align="center" class="filter">
  <tr>
    <td>&nbsp;</td>
    <td>{print_expand_link("project_superset","Projects ")}</td>
    <td>Task Status {get_help("taskList_taskStatus")}</td>
    <td>Created By</td> 
    <td rowspan="6" valign="top">
    
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
    
    </td>
  </tr>
  <tr>
    <td valign="top" align="right" rowspan="8">
      <div id="project_superset" style="display:none">
        <label for="pt_mine">My Projects</label><input type="radio" id="pt_mine" name="projectType" value="mine" onClick="refreshProjectList(this)"{$projectType_checked_mine}><br/>
        <nobr><label for="pt_pm">My Project Managed</label><input type="radio" id="pt_pm" name="projectType" value="pm" onClick="refreshProjectList(this)"{$projectType_checked_pm}></nobr><br/>
        <label for="pt_tsm">My Time Sheet Rec.</label><input type="radio" id="pt_tsm" name="projectType" value="tsm" onClick="refreshProjectList(this)"{$projectType_checked_tsm}><br/>
        <label for="pt_curr">Current</label><input type="radio" id="pt_curr" name="projectType" value="curr" onClick="refreshProjectList(this)"{$projectType_checked_curr}><br/>
        <label for="pt_pote">Potential</label><input type="radio" id="pt_pote" name="projectType" value="pote" onClick="refreshProjectList(this)"{$projectType_checked_pote}><br/>
        <label for="pt_arch">Archived</label><input type="radio" id="pt_arch" name="projectType" value="arch" onClick="refreshProjectList(this)"{$projectType_checked_arch}><br/>
        <label for="pt_all">Everything</label><input type="radio" id="pt_all" name="projectType" value="all" onClick="refreshProjectList(this)"{$projectType_checked_all}><br/>
      </div>
      &nbsp;
    </td>
    <td valign="top" rowspan="8" style="width:275px"><div id="projectListDropdown">{$projectOptions}</div></td>
    <td valign="top"><select name="taskStatus" size="1">{$taskStatusOptions}</select></td>
    <td valign="top"><select name="creatorID">{$creatorPersonOptions}</select></td>  
  </tr>
  <tr>
    <td>Task Type</td>
    <td>Managed By</td>
  </tr>
  <tr>
    <td valign="top" rowspan="6"><select name="taskTypeID[]" size="6" multiple="true">{$taskTypeOptions}</select></td>
    <td><select name="managerID">{$managerPersonOptions}</select></td>
  </tr>
  <tr>
    <td>Assigned To</td>
  </tr>
  <tr>
    <td valign="top"><select id="personID" name="personID">{$personOptions}</select></td>
  </tr>
  <tr>
    <td class="nobr" colspan="1" valign="bottom">
    </td>
    <td class="right" valign="bottom">
      <input type="submit" name="applyFilter" value="Filter"> {get_help("taskList_filter")}
    </td>
  </tr>
</table>

</form>
