      <form action="{$url_alloc_timeSheetList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>User Name</td>
          <td>Start Date From</td>
          <td>Start Date To</td>
          <td>Status</td>
          <td>&nbsp;</td>
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
          <td>
            <select name="status">
              <option value=""> </option>
              {$show_status_options}
            </select>
          </td>
        </tr>
        <tr>
         <td>Project</td>
        </tr>
        <tr>
          <td colspan="3">
            <select name="projectID">
              <option value=""> </option>
              {$show_project_options}
            </select>
          </td>
          <td class="right"><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
      </form>

