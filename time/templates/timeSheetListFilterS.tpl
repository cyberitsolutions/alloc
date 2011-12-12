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
            <select name="personID">
              <option value="">
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
          <td colspan="3">
            <select name="projectID">
              <option value=""> </option>
              {$show_project_options}
            </select>
          </td>
          <td class="right">
            <label for="showFinances">Money</label>
            <input id="showFinances" type="checkbox" name="showFinances" value="1" {print $showFinances ? " checked" : ""}>
          </td>
        </tr>
        <tr>
          <td colspan="4">&nbsp;</td>
          <td class="right"><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

