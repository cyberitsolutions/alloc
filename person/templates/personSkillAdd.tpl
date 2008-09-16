{page::header()}
{page::toolbar()}

<form action="{$url_alloc_personSkillAdd}" method="post">
<table class="box">
  <tr>
    <th colspan="2">Add/Delete New Skill</th>
    <th class="right"><a href={$url_alloc_personSkillMatrix}>Skill Matrix</a></th>
  </tr>
  <tr>
    <td>Skill Class</td>
    <td>Skill Name</td>
    <td></td>
  </tr>
  <tr>
    <td>
      <select name="new_skill_class">{$new_skill_classes}</select>
      <input name="other_new_skill_class" type="text">
    </td>
    <td>
      <select name="new_skill_name">{$new_skills}</select>
      <input name="other_new_skill_name" type="text">
    </td>
    <td>
      <input type="submit" name="add_skill" value="Add">
      <input type="submit" name="delete_skill" value="Delete">
    </td>
  </tr>
</table>
</form>

{page::footer()}
