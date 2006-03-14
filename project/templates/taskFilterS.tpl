      <table class="filter" align="center">
        <tr>
          <td>Project</td>
          <td>Task Status</td>
          <td>Task Type</td>
          <td>Allocated To</td>
          <td>View</td>
          <td>Details</td>
   <!--       <td rowspan="2">{:help_button taskSummaryFilter}</td> -->
          <td></td>
        </tr>
        <tr>
          <td><select name="projectID"><option value=""> -- ALL -- {projectOptions}</select></td>
          <td><select name="taskStatus"><option value=""> -- ALL -- {taskStatusOptions}</select></td>
          <td><select name="taskType"><option value=""> -- ALL -- {taskTypeOptions}</select></td>
          <td>{personSelect}</td>
          <td><select name="taskView">{taskViewOptions}</select></td>
          <td><input type="checkbox" name="show_details"{show_details_checked}></td>
          <td><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
