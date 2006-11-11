{$table_box}
<tr>
  <th colspan="2">Areas of expertise</th>
  <th class="right"><a href={$url_alloc_personSkillMatrix}>Full Skill Matrix </a></th>
</tr>
<tr>
  <td>Skill</td>
  <td>Proficiency</td>
  <td></td>
</tr>
  {show_person_areasOfExpertise("templates/personExpertiseItemView.tpl")}
<tr>
  <td colspan="3"><hr></td>
</tr>
<tr>
  <td>
    <form action="{$url_alloc_person}" method=post>
    <input type="hidden" name="personID" value="{$person_personID}">
    <select name="skillID[]">{$skills}</select>
  </td>
  <td>
    <select name="skillProficiency">
      <option value="Novice">Novice
      <option value="Junior">Junior
      <option value="Intermediate">Intermediate
      <option value="Advanced">Advanced
      <option value="Senior">Senior
    </select>
  </td>
  <td>
    <input type="submit" name="personExpertiseItem_add" value="Add">
  </td>
</tr>
</table>
</form>
