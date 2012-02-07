      <form action="{$url_alloc_reminderList}" method="get">
      <table class="filter corner">
        <tr>
          <td>Recipient</td>
          <td>Active</td>
          <td></td>
        </tr>
        <tr>
          <td><select name="filter_recipient"><option value="">{$recipientOptions}</select></td>
          <td><select name="filter_reminderActive"><option value="">{$reminderActiveOptions}</select></td>
          <td><input type="submit" name="filter" value="Filter"></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
