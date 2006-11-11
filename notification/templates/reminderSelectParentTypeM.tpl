{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Add Reminder - Select Type</th>
  </tr>
  <tr>
    <td>
      <form action="{$url_alloc_reminderAdd}" method="post">
        Type:
        <select name="parentType">
          {$parentTypeOptions}
        </select>
        
        <input type="hidden" name="step" value="2">
        <input type="submit" name="submitParentType" value="Next">
      </form>
    </td>
  </tr>
</table>

{show_footer()}
