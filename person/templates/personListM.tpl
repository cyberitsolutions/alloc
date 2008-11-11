{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>People</th> 
    <th class="right" colspan="8">&nbsp;&nbsp;<a href={$url_alloc_personGraph}>Person Graphs</a>&nbsp;&nbsp;<a href={$url_alloc_personSkillMatrix}>Skill Matrix</a>&nbsp;&nbsp;<a href="{$url_alloc_person}">New Person</a>
    </th>
  </tr>
  <tr>
    <td colspan="9" align="center">
      {show_filter()}
    </td>
  </tr>
  <tr>
    <td colspan="9">
      {show_people()}
    </td>
  </tr>
</table>
{page::footer()}
