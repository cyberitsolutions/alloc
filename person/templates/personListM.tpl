{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>People</th> 
    <th class="right" colspan="8">&nbsp;&nbsp;<a href={$url_alloc_personGraph}>Person Graphs</a></td>{$personAddSkill_link}&nbsp;&nbsp;<a href="{$url_alloc_person}">New Person</a>
    </th>
  </tr>
  <tr>
    <td colspan="9" align="center">
      <form action="{$url_alloc_personList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td><select name="skill_class">{$skill_classes}</select></td>
          <td><select name="skill">{$skills}</select></td>
          <td><select name="expertise">{$employee_expertise}</select></td>
          <td><input type="checkbox" name="show_all_users"{$show_all_users_checked}>Show All Users<br/>
              <input type="checkbox" name="show_skills"{$show_skills_checked}>Show Skills List</td>
          <td><input type="submit" value="Filter"></td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <!-- <th>Select</th> -->
    <td>Name</td>
    <td>Enabled</td>
    <td>Contact</td>
    <td>Actions</th>
    <td>Sum Prev Fort.</td>
    <td>Avg Per Fort.</td>
{if check_optional_show_skills_list()}
    <td>
      Senior
      <img src="../images/skill_senior.png" alt="S" align="absmiddle">
      <img src="../images/skill_advanced.png" alt="A" align="absmiddle">
      <img src="../images/skill_intermediate.png" alt="I" align="absmiddle">
      <img src="../images/skill_junior.png" alt="J" align="absmiddle">
      <img src="../images/skill_novice.png" alt="N" align="absmiddle"> Novice
    </td>
{/}

  </tr>
  {show_people("templates/personListR.tpl")}
</table>
{show_footer()}
