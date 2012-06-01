{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Add Reminder - Select Type</th>
  </tr>
  <tr>
    <td>
      <form action="{$url_alloc_reminder}" method="post">
        Type:
        <select name="parentType">
          {$parentTypeOptions}
        </select>
        
        <input type="hidden" name="step" value="2">
        <button type="submit" name="submitParentType" value="1" class="save_button">Next<i class="icon-arrow-right"></i></button>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
</table>

{page::footer()}
