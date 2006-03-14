<form action="{url_alloc_tf}" method="post">
<tr>
  <td>
    <select name="person_personID">
      <option value="">
      {:show_person_options}
    </select>
  </td>
  <td align="center">
    {person_buttons}
  </td>
</tr>
<input type="hidden" name="person_tfPersonID" value="{person_tfPersonID}">
<input type="hidden" name="tfID" value="{tfID}">
</form>
