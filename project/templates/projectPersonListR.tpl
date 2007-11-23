<form action="{$url_alloc_project}" method="post">
<tr>
  <td><select name="person_personID"><option value="">{show_person_options()}</select> </td>
  <td><select name="person_projectPersonRoleID">{$person_projectPersonRole_options}</select></td>
  <td>$<input type="text" size="7" name="rate" value="{$person_rate}" />(ex. {$taxName})</td>
  <td><select name="rateUnitID">{$rateType_options}</select></td>
  <td>{$person_buttons}</td>
</tr>

<input type="hidden" name="person_projectPersonID" value="{$person_projectPersonID}">
<input type="hidden" name="projectID" value="{$project_projectID}">
</form>
