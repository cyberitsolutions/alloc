{show_header()}
  {show_toolbar()}
<form action="{$url_alloc_tf}" method="post">
<input type="hidden" name="tfID" value="{$tfID}">

{$table_box}
  <tr>
    <th colspan="2">Tagged Fund</th>
  </tr>
  <tr>
    <td width="30%">TF Name: </td>
    <td><input type="text" size="30" maxlength="255" name="tfName" value="{$tfName}"></td> 
  </tr>
  <tr>
    <td>Quick Payroll Employee Number: </td>
    <td><input type="text" size="30" maxlength="10" name="qpEmployeeNum" value="{$qpEmployeeNum}"></td> 
  </tr>
  <tr>
    <td>Quicken Account Name: </td>
    <td><input type="text" size="30" maxlength="255" name="quickenAccount" value="{$quickenAccount}"></td> 
  </tr>
  <tr>
    <td>Status: </td>
    <td><select name="status">
      {$status_dropdown}
      </select>
    </td>
  </tr>
  <tr>
    <td>Comments: </td>
    <td><textarea rows="4" cols="30" wrap="virtual" name="tfComments">{$tfComments}</textarea></td>
  </tr>
  <tr>
    <td>Last Modified by:</td>
    <td>{$tfModifiedUser} {$tfModifiedTime}</td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
    <input type="submit" name="delete" value="Delete Record" onClick="return confirm('Are you sure you want to delete this record?')"></td>
  </tr>
</table>
</form>


{$table_box}
  <tr>
    <th colspan="2">TF Owner(s)</th>
  </tr>
  <tr>
    <td>
      {show_person_list("templates/tfPersonListR.tpl")}
      {show_new_person("templates/tfPersonListR.tpl")}
    </td>
  </tr>
</table>





{show_footer()}

