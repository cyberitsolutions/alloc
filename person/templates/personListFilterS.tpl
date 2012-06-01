      <form action="{$url_alloc_personList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td><select name="skill_class">{$skill_classes}</select></td>
          <td><select name="skill">{$skills}</select></td>
          <td><select name="expertise">{$employee_expertise}</select></td>
          <td valign="top">
            <table class="filter corner" align="center" width="95%">
              <tr>
                <td align="right" class="nobr"><label for="personActive">Only Active Users</label></td>
                <td align="right" width="1%"><input type="checkbox" value="1" id="personActive" name="personActive"{$show_all_users_checked}></td>
                <td align="right" class="nobr"><label for="showSkills">Skills</label></td>
                <td align="right" width="1%"><input type="checkbox" value="1" id="showSkills" name="showSkills"{$show_skills_checked}></td> 
                {if $current_user->have_perm(PERM_PERSON_READ_MANAGEMENT)}
                <td align="right" class="nobr"><label for="showHours">Hours</label></td>
                <td align="right" width="1%"><input type="checkbox" value="1" id="showHours" name="showHours"{$show_hours_checked}></td> 
                {/}
              </tr>
            </table>
          </td>
          <td><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
        </tr>
        <tr>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
