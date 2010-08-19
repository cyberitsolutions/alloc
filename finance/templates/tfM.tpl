{page::header()}
  {page::toolbar()}
<form action="{$url_alloc_tf}" method="post">
<input type="hidden" name="tfID" value="{$tfID}">

<table class="box">
  <tr>
    <th colspan="2">Tagged Fund</th>
  </tr>
  <tr>
    <td width="20%">TF Name: </td>
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
    <td>Enabled: </td>
    <td><input type="checkbox" name="isActive" value="1"{$tfIsActive}></td>
  </tr>
  <tr>
    <td valign="top">Comments: </td>
    <td>{page::textarea("tfComments",$TPL["tfComments"],array("cols"=>30))}</td>
  </tr>
  <tr>
    <td>Last modified by:</td>
    <td>{=$tfModifiedUser} {$tfModifiedTime}</td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="save" value="Save">
    <input type="submit" name="delete" value="Delete" class="delete_button"></td>
  </tr>
</table>
</form>

{if $tfID}
<table class="box">
  <tr>
    <th colspan="2">TF Owners</th>
  </tr>
  <tr>
    <td>
      {show_person_list("templates/tfPersonListR.tpl")}
      {show_new_person("templates/tfPersonListR.tpl")}
    </td>
  </tr>
</table>
{/}




{page::footer()}

