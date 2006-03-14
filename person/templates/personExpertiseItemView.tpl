{optional:person_skills_header}
<tr>
  <td colspan="3"><hr><strong>{skillClass}</strong></td>
</tr>
{/optional}
<form action="{url_alloc_person}" method="post">
<tr>
  <td>
    <input type="hidden" name="personID" value="{person_personID}">
    <input type="hidden" name="proficiencyID" value="{proficiencyID}">
    <input type="hidden" name="skillID" value="{skillID}">
    {skillName}
  </td>
  <td><select name="skillProficiency">{skill_proficiencys}</select></td>
  <td><nobr>{personExpertiseItem_buttons}</nobr></td>
</tr>
</form>
