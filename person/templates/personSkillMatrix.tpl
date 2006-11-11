{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="200">Skill Matrix</th>
  </tr> 
  <tr>
    <td colspan="200" align="center">
    {show_filter("templates/personSkillFilterS.tpl")}
    </td>
  </tr>
  <tr>
    <td colspan="200">Key:
      <img src="../images/skill_senior.png" alt="S" align="absmiddle"> Senior,
      <img src="../images/skill_advanced.png" alt="A" align="absmiddle"> Advanced,
      <img src="../images/skill_intermediate.png" alt="I" align="absmiddle"> Intermediate,
      <img src="../images/skill_junior.png" alt="J" align="absmiddle"> Junior,
      <img src="../images/skill_novice.png" alt="N" align="absmiddle"> Novice
    </td>
  </tr>
  {show_skill_expertise()}
</table>
{show_footer()}
