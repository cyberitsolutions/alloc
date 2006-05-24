<form action="{url_alloc_home}" method="post">
<table align="center" width="100%">
  <tr>
    <td colspan="2"><b>Look and Feel</b></td>
  </tr>
  <tr>
    <td>Font Size</td>
    <td>Theme</td>
  </tr>
  <tr>
    <td>
      <select name="font">
      {fontOptions}
      </select>
    </td>
    <td>
      <select name="theme">
      {themeOptions}
      </select>
    </td>
  </tr>
  
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>

  <tr>
    <td colspan="2"><b>Top Tasks</b></td>
  </tr>
  <tr>
    <td>Number Tasks</td>
    <td>Status</td>
  </tr>
  <tr>
    <td>
      <select name="topTasksNum">
      {topTasksNumOptions}
      </select>
    </td>
    <td>
      <select name="topTasksStatus">
      {topTasksStatusOptions}
      </select>
    </td>
  </tr>

  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
        
  <tr>
    <td colspan="2"><b>Task Calendar</b></td>
  </tr>
  <tr>
    <td>Number Weeks</td>
    <td>Weeks Back</td>
  </tr>
  <tr>
    <td>
      <select name="weeks">
      {weeksOptions}
      </select>
    </td>
    <td>
      <select name="weeksBack">
      {weeksBackOptions}
      </select>
    </td>
  </tr>
 
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
        
  <tr>
    <td colspan="2"><b>Project List</b></td>
  </tr>
  <tr>
    <td>Number Projects</td>
    <td></td>
  </tr>
  <tr>
    <td>
      <select name="projectListNum">
      {projectListNumOptions}
      </select>
    </td>
    <td align="right"><input type="submit" name="customize_save" value="Save"></td>
  </tr>
</table>

</form>
