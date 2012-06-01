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
          <td>
            <button type="submit" name="filter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
