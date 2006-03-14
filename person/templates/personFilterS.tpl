    <form action="{url_alloc_personList}" method="post">
    <table class="filter" align="center">
      <tr>
        <td><select name="skill_class">{skill_classes}</select></td>
        <td><select name="skill">{skills}</select></td>
        <td><select name="expertise">{employee_expertise}</select></td>
        <td><input type="checkbox" name="show_skills" {show_skills_checked}>Show Skills List</td>
        <td><input type="submit" value="Filter"></td>
      </tr>
    </table>
    </form>
