    <form action="{$url_alloc_projectList}" method="post">
      <table class="filter">
        <tr>
          <td>Status</td>
          <td>Type</td>
          <td>Allocated To</td>
          <td>Name Containing</td>
          <td></td>
    <!--      <td rowspan="2">{help_button("projectListFilter")}</td> -->
        </tr>
        <tr>
          <td><select name="projectStatus"><option value=""> {$projectStatusOptions}</select></td>
          <td><select name="projectType"><option value=""> {$projectTypeOptions}</select></td>
          <td>{$personSelect}</td>
          <td><input type="text" name="projectName" value="{$projectName}"></td>
          <td><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
    </form>
