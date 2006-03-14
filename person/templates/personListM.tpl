{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Personnel</th> 
    <th class="right" colspan="7">
      &nbsp;&nbsp;<a href="{url_alloc_person}">New Person</a>
      &nbsp;&nbsp;<a href={url_alloc_personSkillMatrix}>Full Skill Matrix</a>
      &nbsp;&nbsp;<a href={url_alloc_personGraphs}>Person Graphs</a></td>
    </th>
  </tr>
  <tr>
    <td colspan="7" align="center">{:show_filter templates/personFilterS.tpl}</td>
  </tr>
  <tr>
    <!-- <th>Select</th> -->
    <td>Name</td>
    <td>Enabled</td>
    <td>Last Login</td>
    <td>Availability</td>
{optional:show_skills_list}
        <th>Areas of Expertise
          (<img src="../images/skill_senior.jpg" alt="S" width=18 height=18 align="absmiddle"> Senior.
          <img src="../images/skill_advanced.jpg" alt="A" width=18 height=18 align="absmiddle">.
          <img src="../images/skill_intermediate.jpg" alt="I" width=18 height=18 align="absmiddle">.
          <img src="../images/skill_junior.jpg" alt="J" width=18 height=18 align="absmiddle">.
          <img src="../images/skill_novice.jpg" alt="N" width=18 height=18 align="absmiddle"> Novice)
        </th>
{/optional}
        <td>Actions</th>
        <td><nobr>On Leave</nobr></td>
        <td>Hours Worked for the Last 14 Days</td>
        <td>Avg per Fortnight</td>
      </tr>
      {:show_people templates/personListR.tpl}
    </table>
    {:show_add_skill templates/personSkillAdd.tpl}
{:show_footer}
