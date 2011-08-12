<form action="{$url_alloc_person}" method="post">
{if check_optional_person_skills_header()}
<tr>
  <th colspan="3">{print $skillClass ? $skillClass : "Other"}</th>
</tr>
{/}
<tr>
  <td width="70%">
    <input type="hidden" name="personID" value="{$person_personID}">
    <input type="hidden" name="proficiencyID" value="{$proficiencyID}">
    <input type="hidden" name="skillID" value="{$skillID}">
    {$skillName}
  </td>
  <td class="right">
    <select name="skillProficiency">{$skill_proficiencys}</select>
  </td>
  <td class="right" width="20%">
    <nobr>{$personExpertiseItem_buttons}</nobr>
  </td>
</tr>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
