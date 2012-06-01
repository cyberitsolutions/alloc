<form action="{$url_alloc_tf}" method="post">
<tr>
  <td style="width:1%;">
    <select name="person_personID">
      <option value="">
      {show_person_options()}
    </select>
  </td>
  <td align="left">
    {$person_buttons}
  </td>
</tr>
<input type="hidden" name="person_tfPersonID" value="{$person_tfPersonID}">
<input type="hidden" name="tfID" value="{$tfID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
