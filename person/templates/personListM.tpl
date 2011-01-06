{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">People
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href={$url_alloc_personGraph}>Person Graphs</a>
        <a href={$url_alloc_personSkillMatrix}>Skill Matrix</a>
        <a href="{$url_alloc_person}">New Person</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      {show_filter()}
    </td>
  </tr>
  <tr>
    <td>
      {show_people()}
    </td>
  </tr>
</table>
{page::footer()}
