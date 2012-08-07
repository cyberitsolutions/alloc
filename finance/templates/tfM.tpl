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
    <td>{page::textarea("tfComments",$tfComments,array("cols"=>30))}</td>
  </tr>
  <tr>
    <td>Last modified by:</td>
    <td>{=$tfModifiedUser} {$tfModifiedTime}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
    <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
    <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
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

