{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Project Task Summary</th>
    <th class="right">{$navigation_links}</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{$url_alloc_projectSummary}" method="post">
      <input type="hidden" name="projectID" value="{$projectID}">
        <table class="filter" align="center">
          <tr>
            <td>Task Type</td>
            <td>Task Status</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td rowspan="2"><select name="taskTypeID[]" size="6" multiple="true">{$taskTypeOptions}</select></td>
            <td valign="top"><select name="taskStatus" size="1">{$taskStatusOptions}</select></td>
            <td></td>
          </tr>
          <tr>
            <td valign="bottom">&nbsp;</td>
            <td align="right" valign="bottom"><input type="submit" name="applyFilter" value="Filter">&nbsp;{help_button("projectSummary_filter")}</td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2"><img src="{$url_alloc_projectGraph}projectID={$projectID}&taskTypeID={$taskTypeID_url}&taskStatus={$taskStatus}" alt="Project Graph"></td>
  </tr>
  <tr>
    <td colspan="2">
        {$task_summary}
    </td>
  </tr>
</table>
{show_footer()}
