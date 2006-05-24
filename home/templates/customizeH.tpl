<form action="{url_alloc_home}" method="post">
<table align="center" width="100%">
  <tr>
    <td colspan="3"><b>Look and Feel</b></td>
  </tr>
  <tr>
    <td>Font Size</td>
    <td>Theme</td>
    <td></td>
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
    <td colspan="3"><b>Home Task Calendar</b></td>
  </tr>
  <tr>
    <td>Num Weeks</td>
    <td>Weeks Back</td>
    <td></td>
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
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><b>Home Top Tasks</b></td>
  </tr>
  <tr>
    <td>Num Tasks</td>
    <td>Status</td>
    <td></td>
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




    
    <td ><input type="submit" name="customize_save" value="Save"></td>
  </tr>
</table>

</form>
