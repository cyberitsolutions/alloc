{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Skill Matrix
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        {if $current_user->have_perm(PERM_PERSON_READ_MANAGEMENT)}
          <a href="{$url_alloc_personSkillAdd}">Edit Skill Items</a>
        {/}
      </span>
    </th>
  </tr> 
  <tr>
    <td align="center">
      <table>
        <tr>
          <td>{show_filter("templates/personSkillFilterS.tpl")}</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td><img src="../images/skill_senior.png" alt="Senior" align="absmiddle"> Senior,
      <img src="../images/skill_advanced.png" alt="Advanced" align="absmiddle"> Advanced,
      <img src="../images/skill_intermediate.png" alt="Intermediate" align="absmiddle"> Intermediate,
      <img src="../images/skill_junior.png" alt="Junior" align="absmiddle"> Junior,
      <img src="../images/skill_novice.png" alt="Novice" align="absmiddle"> Novice
    </td>
  </tr>
  <tr>
    <td>
      <table>
        {show_skill_expertise()}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
