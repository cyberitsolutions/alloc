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
    <td><b>TF Name</td>
    <td><b>Description</td>
    <td align="right"><b>Balance</td>
    <td align="center"><b>Action</td>
  </tr>
  {show_tf("templates/tfListR.tpl")}
  <tr>
    <td>Total:  ${$grand_total}</td>
  </tr>
</table>



{show_footer()}
