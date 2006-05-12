<form action="{url_alloc_home}" method="post">
<table align="center" width="100%">
  <tr>
    <th colspan="2">Sitewide</th>
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
    <th colspan="2">Homepage Calendar</th>
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
    <td ><input type="submit" name="customize_save" value="Save"></td>
  </tr>
</table>

</form>
