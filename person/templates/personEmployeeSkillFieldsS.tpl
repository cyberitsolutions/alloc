<table class="box">
<tr>
  <th class="header">Areas of Expertise
    <span>
      <a href={$url_alloc_personSkillMatrix}>Skill Matrix</a>
    </span>
  </th>
</tr>
<tr>
  <td>
    <table class="list">
{show_person_areasOfExpertise("templates/personExpertiseItemView.tpl")}
    </table>
  </td>
</tr>
<tr>
  <td>
    <form action="{$url_alloc_person}" method=post>
    <table class="list">
      <tr>
        <td width="70%">
          <input type="hidden" name="personID" value="{$person_personID}">
          <select name="skillID[]">{$skills}</select>
        </td>
        <td class="right">
          <select name="skillProficiency">
            <option value="Novice">Novice
            <option value="Junior">Junior
            <option value="Intermediate">Intermediate
            <option value="Advanced">Advanced
            <option value="Senior">Senior
          </select>
        </td>
        <td class="right" width="20%">
          <button type="submit" name="personExpertiseItem_add" value="1" class="save_button">Add<i class="icon-plus-sign"></i></button>
        </td>
      </tr>
    </table>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
