{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="4">TF List</th>
  </tr>
  <tr>
    <td colspan="4">
      <form action="{$url_alloc_tfList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td><input type="checkbox" name="owner"{$owner_checked}> Owner</td>
	  <td><input type="checkbox" name="showall"{$showall_checked}> Show All</td>
          <td><input type="submit" name="apply_filter" value="Filter"></td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="4">
      <table class="tasks" border="0" cellspacing="0">
        <tr>
          <th class="col">TF Name</th>
          <th class="col">Description</th>
          <th class="col" align="right">Balance</th>
          <th class="col" align="center"></th>
        </tr>
        {show_tf("templates/tfListR.tpl")}
        <tr>
          <td class="col" colspan="3">&nbsp;</td>
          <td class="col">Total:  ${$grand_total}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>



{show_footer()}
