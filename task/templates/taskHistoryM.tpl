<table class="box">
  <thead>
    <tr>
      <th>Task History</th>
      <th class="right"><a href="#x" class="magic" onclick="$('#taskHistoryTable').slideToggle('fast');">Expand</a></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2">
        <div class="hidden" id="taskHistoryTable">
        <table class="sortable list">
          <thead>
            <tr><th>Date</th><th>Change</th><th>Made by</th></tr>
          </thead>
          <tbody>
            {$changeHistory}
          </tbody>
        </table>
        </div>
      </td>
    </tr>
  </tbody>
</table>
