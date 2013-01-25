<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function refreshProjectList(show_all) {
  url = '{$url_alloc_updateTimeSheetProjectList}'+(!show_all.checked?'current=true':'');
  makeAjaxRequest(url,'projectDropdown',{  },1);
}
</script>


<form action="{$url_alloc_timeSheetList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>User Name</td>
          <td>Start Date From</td>
          <td>Start Date To</td>
          <td>Status</td>
        </tr>
        <tr>
          <td>
            <select name="personID[]" multiple="true">
              {$show_userID_options}
            </select>
          </td>
          <td>{page::calendar("dateFrom",$dateFrom)}</td>
          <td>{page::calendar("dateTo",$dateTo)}</td>
          <td><select name="status[]" multiple="true">{$show_status_options}</select></td>
        </tr>
        <tr>
         <td colspan="3">Project</td>
        </tr>
        <tr>
          <td colspan="3" style='vertical-align:top'>
            <span id="projectDropdown">
            <select name="projectID[]" multiple="true">
              {$show_project_options}
            </select>
            </span>
          </td>
          <td class="right">
              <label for="showAllProjects">Show all projects</label>
              <input id="showAllProjects" type="checkbox" name="showAllProjects" onclick="refreshProjectList(this);" {print $showAllProjects ? " checked" : ""}>
            <br>
            <label for="showFinances">Money</label>
            <input id="showFinances" type="checkbox" name="showFinances" value="1" {print $showFinances ? " checked" : ""}>
          </td>
        </tr>
        <tr>
          <td colspan="4">&nbsp;</td>
          <td class="right"><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

