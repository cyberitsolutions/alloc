{show_header()}
{show_toolbar()}
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
          <td rowspan="3" valign="top">

            <table class="filter" align="center" width="95%">
              <tr>
                <td valign="top" colspan="2"><b><nobr>Display Options</nobr></b></td>
              </tr>
              <tr>
                <td align="right" class="nobr">Show All Users</td>
                <td align="right" width="1%"><input type="checkbox" name="show_all_users"{$show_all_users_checked}></td>
              </tr>
              <tr>
                <td align="right" class="nobr">Show Skills List</td>
                <td align="right" width="1%"><input type="checkbox" name="show_skills"{$show_skills_checked}></td> 
              </tr>
            </table>

          </td>
        </tr>
        <tr>
          <td><select name="skill">{$skills}</select></td>
          <td></td>
        </tr>
        <tr>
          <td valign="top"><select name="expertise">{$employee_expertise}</select></td>
          <td valign="bottom"><input type="submit" value="Filter"></td>
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
          <th>Sum Prev Fort.</th>
          <th>Avg Per Fort.</th>
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
{show_footer()}
