<form action="{url_alloc_home}" method="post">
<table cellspacing="0" style="width:100%">
  <tr>
    <td colspan="4"><b>Look and Feel</b></td>
  </tr>
  <tr>
    <td>Font Size</td>
    <td>
      <select name="font">
      {fontOptions}
      </select>
    </td>
    <td>Theme</td>
    <td>
      <select name="theme">
      {themeOptions}
      </select>
    </td>
  </tr>

  <tr>
    <td colspan="4"><b>Top Tasks</b></td>
  </tr>
  <tr>
    <td>No. Tasks</td>
    <td>
      <select name="topTasksNum">
      {topTasksNumOptions}
      </select>
    </td>
    <td>Status</td>
    <td>
      <select name="topTasksStatus">
      {topTasksStatusOptions}
      </select>
    </td>
  </tr>

  <tr>
    <td colspan="4"><b>Task Calendar</b></td>
  </tr>
  <tr>
    <td>No. Weeks</td>
    <td>
      <select name="weeks">
      {weeksOptions}
      </select>
    </td>
    <td>Back</td>
    <td>
      <select name="weeksBack">
      {weeksBackOptions}
      </select>
    </td>
  </tr>
 
  <tr>
    <td colspan="4"><b>Project List</b></td>
  </tr>
  <tr>
    <td class="nobr">No. Projects</td>
    <td>
      <select name="projectListNum">
      {projectListNumOptions}
      </select>
    </td>
    <td></td>
    <td align="center"><input type="submit" name="customize_save" value="Save"></td>
  </tr>
</table>

</form>
