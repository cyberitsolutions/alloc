<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function refreshProjectList(show_all) {
  url = '{$url_alloc_updateTimeSheetProjectList}'+(!show_all.checked?'current=true':'');
  makeAjaxRequest(url,'projectDropdown');
}
</script>


<form action="{$url_alloc_productSaleList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>User Name</td>
          <td>Status</td>
          <td colspan="3">Project</td>
        </tr>
        <tr>
           <td>
            <select name="personID">
              <option value="">
              {$show_userID_options}
            </select>
          </td>
          <td><select name="status[]" multiple="true">{$show_status_options}</select></td>
          <td>
            <span id="projectDropdown">
            <select name="projectID">
              <option value=""> </option>
              {$show_project_options}
            </select>
            </span>
              <input id="showAllProjects" type="checkbox" name="showAllProjects" onclick="refreshProjectList(this);" {print $showAllProjects ? " checked" : ""}>
              <label for="showAllProjects">Show all projects</label>
          </td>
          <td class="right"><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

