    <form action="{$url_alloc_personSkillMatrix}" method="get">
    <table class="filter corner" align="center">
      <tr>
        <td colspan=2>Skill(s)</td>
<!--
        <td rowspan=2><input name="show_all" type="checkbox">Force all</td>
-->
        <td></td>
      </tr>
      <tr>
        <td><select name="skill_class">{$skill_classes}</select></td>
        <td><select name="talent">{$skills}</select></td>
        <td><input type="submit" value="Filter"></td>
      </tr>
    </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
