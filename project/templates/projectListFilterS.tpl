    <form action="{$url_alloc_projectList}" method="get">
      <table class="filter corner">
        <tr>
          <td>Status</td>
          <td>Type</td>
          <td>Allocated To</td>
          <td>Name Containing</td>
          <td></td>
    <!--      <td rowspan="2">{page::help("projectListFilter")}</td> -->
        </tr>
        <tr>
          <td><select name="projectStatus[]" multiple="true">{$projectStatusOptions}</select></td>
          <td><select name="projectType[]" multiple="true">{$projectTypeOptions}</select></td>
          <td>{$personSelect}</td>
          <td><input type="text" name="projectName" value="{$projectName}"></td>
          <td><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
        </tr>
      </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
