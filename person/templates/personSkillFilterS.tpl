    <form action="{$url_alloc_personSkillMatrix}" method="get">
    <table class="filter corner" align="center">
      <tr>
        <td colspan=2>Skill(s)</td>
        <td></td>
      </tr>
      <tr>
        <td><select name="skill_class">{$skill_classes}</select></td>
        <td><select name="talent">{$skills}</select></td>
        <td>
          <button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
        </td>
      </tr>
    </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
