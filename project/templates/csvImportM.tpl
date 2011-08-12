{page::header()}
{page::toolbar()}
<form action="{=$url_alloc_importCSV}" method="post">
<input type="hidden" name="projectID" value="{=$projectID}">
<input type="hidden" name="filename" value="{=$filename}">
<table class="box">
  <tr>
  <th>CSV Import</th>
  </tr>
  <tr>
  <td>
  <table class="list">
  <tr>
    <th>Value</th>
    <th>Sample</th>
    <th>Sample</th>
    <th>Sample</th>
  </tr>
  {foreach $rows as $row}
  <tr>
    <td><select name="columns[]">{echo $row['dropdown']}</select></td>
    <td style="width: 20%">{echo $row['cols'][0]}</td>
    <td style="width: 20%">{echo $row['cols'][1]}</td>
    <td style="width: 20%">{echo $row['cols'][2]}</td>
  </tr>
  {/}
  <tr>
  <td></td>
  <td><input type="checkbox" name="headerRow" {$headerRow}><label for="header">Header row</label></td>
  <td colspan="2">
  </tr>

  <tr>
  <td colspan="4" class="center" ><input type="submit" name="import" value="Import CSV"></td>
  </tr>
  </table>
  
  </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
