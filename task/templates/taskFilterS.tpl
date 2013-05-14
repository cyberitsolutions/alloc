<form action="{$url_form_action}" method="get">
<table align="center" class="filter corner">
  <tr>
    <td>&nbsp;</td>
    <td>{page::expand_link("project_superset","Projects")}</td>
    <td>Created By</td> 
    <td rowspan="6" valign="top" colspan="3" class="right">
      {if !$hide_field_options}
      <table align="right">
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
          <td align="right"><label for="showTimes" class="nobr">Estimates</label></td>
          <td><input type="checkbox" id="showTimes" name="showTimes"{$showTimes_checked}></td>
          <td align="right"><label for="showAssigned" class="nobr">Assigned To</label></td>
          <td><input type="checkbox" id="showAssigned" name="showAssigned"{$showAssigned_checked}></td>
        </tr>
        <tr>
          <td align="right"><label for="showPercent" class="nobr">Progress</label></td>
          <td><input type="checkbox" id="showPercent" name="showPercent"{$showPercent_checked}></td>
          <td align="right"><label for="showProject" class="nobr">Project</label></td>
          <td><input type="checkbox" id="showProject" name="showProject"{$showProject_checked}></td>
        </tr>
        <tr>
          <td align="right"><label for="showPriority" class="nobr">Priority Info</label></td>
          <td><input type="checkbox" id="showPriority" name="showPriority"{$showPriority_checked}></td>
        </tr>
      </table>
      {/}
    </td>
  </tr>
  <tr>
    <td valign="top" align="right" rowspan="8">
      <div id="project_superset" style="display:none">
        <label for="pt_mine">My Projects</label><input type="radio" id="pt_mine" name="projectType" value="mine" onClick="refreshProjectList(this)"{$projectType_checked.mine}><br>
        <nobr><label for="pt_pm">My Project Managed</label><input type="radio" id="pt_pm" name="projectType" value="pm" onClick="refreshProjectList(this)"{$projectType_checked.pm}></nobr><br>
        <label for="pt_tsm">My Time Sheet Recip.</label><input type="radio" id="pt_tsm" name="projectType" value="tsm" onClick="refreshProjectList(this)"{$projectType_checked.tsm}><br>
    
        {$m = new meta("projectStatus")}
        {$ops = $m->get_assoc_array("projectStatusID","projectStatusID")}
        {foreach $ops as $v}
        <label for="pt_{$v}">{$v}</label><input type="radio" id="pt_{$v}" name="projectType" value="{$v}" onClick="refreshProjectList(this)"{$projectType_checked.$v}><br>
        {/}

        <label for="pt_all">Everything</label><input type="radio" id="pt_all" name="projectType" value="all" onClick="refreshProjectList(this)"{$projectType_checked_all}><br>
      </div>
      &nbsp;
    </td>
    <td valign="top" style="width:275px"><div id="projectListDropdown">{$projectOptions}</div></td>
    <td valign="top"><select name="creatorID[]" multiple="true">{$creatorPersonOptions}</select></td>  
  </tr>
  <tr>
    <td>Task Status</td>
    <td>Managed By</td>
  </tr>
  <tr>
    <td valign="top"><select name="taskStatus[]" multiple="true">{$taskStatusOptions}</select></td>
    <td><select name="managerID[]" multiple="true">{$managerPersonOptions}</select></td>
  </tr>
  <tr>
    <td>Task Type</td>
    <td>Assigned To</td>
  </tr>
  <tr>
    <td valign="top"><select name="taskTypeID[]" size="6" multiple="true">{$taskTypeOptions}</select></td>
    <td valign="top"><select id="personID" name="personID[]" multiple="true">{$personOptions}</select></td>
  </tr>
  <tr>
    <td colspan="2">Task Date</td>
    <td class="right" valign="bottom" rowspan="2" style="vertical-align:bottom;">
      <span>
        <select name="limit">
        {$limitOptions}
        </select>
      </span>

      {if $showCancel}
        <a href="#x" onClick="$(this).parents('.config_top_ten_tasks').fadeOut();">Cancel</a>
      {/}
      &nbsp;&nbsp;
      <button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
      {page::help("taskList_filter")}
    </td>
  </tr>
  <tr>
    <td class="nobr" colspan="2" valign="bottom">
      <span style="float:left">
        <select name="taskDate" onChange="$('.hidden').hide(); if ($(this).val()) $('.'+$(this).val()).slideDown('fast');">
          {$taskDateOptions}
        </select>
      </span>
      {if !$dateOne && !$dateTwo}
        {$visibility = "hidden"}
      {/}
      <span style="float:right;" class="{$visibility} d_created d_assigned d_targetStart d_targetCompletion d_actualStart d_actualCompletion d_dates">
        {page::calendar("dateOne",$dateOne);}<div style="display:inline; float:left;">&nbsp;&nbsp;to&nbsp;&nbsp;</div>
        {page::calendar("dateTwo",$dateTwo);}
      </span>
    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>
