{page::header()}
{page::toolbar()}
{$table_box}
  <tr>
    <th>People</th> 
    <th class="right" colspan="8">&nbsp;&nbsp;<a href={$url_alloc_personGraph}>Person Graphs</a>&nbsp;&nbsp;<a href={$url_alloc_personSkillMatrix}>Skill Matrix</a>&nbsp;&nbsp;<a href="{$url_alloc_person}">New Person</a>
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
          <td valign="top">
            <table class="filter" align="center" width="95%">
              <tr>
                <td align="right" class="nobr">All Users</td>
                <td align="right" width="1%"><input type="checkbox" name="show_all_users"{$show_all_users_checked}></td>
                <td align="right" class="nobr">&nbsp;Skills</td>
                <td align="right" width="1%"><input type="checkbox" name="show_skills"{$show_skills_checked}></td> 
              </tr>
            </table>
          </td>
          <td><input type="submit" value="Filter"></td>
        </tr>
        <tr>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="9">
      {$table_list}
        <tr>
          <th>Name</th>
          <th>Enabled</th>
          <th>Contact</th>
          <th>Actions</th>
      {if defined("SHOW_PRIVATE_COLUMNS")}
          <th>Sum Prev Fort.</th>
          <th>Avg Per Fort.</th>
      {/}
      {if check_optional_show_skills_list()}
          <th>
            Senior
            <img src="../images/skill_senior.png" alt="S" align="absmiddle">
            <img src="../images/skill_advanced.png" alt="A" align="absmiddle">
            <img src="../images/skill_intermediate.png" alt="I" align="absmiddle">
            <img src="../images/skill_junior.png" alt="J" align="absmiddle">
            <img src="../images/skill_novice.png" alt="N" align="absmiddle"> Novice
          </th>
      {/}

        </tr>
        {show_people("templates/personListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
