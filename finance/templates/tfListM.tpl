{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="4">TF List</th>
  </tr>
  <tr>
    <td colspan="4" align="center">
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
      {$table_list}
        <tr>
          <th>TF Name</th>
          <th>Description</th>
          <th align="right">Balance</th>
          <th align="center"></th>
        </tr>
        {show_tf("templates/tfListR.tpl")}
        <tfoot>
        <tr>
          <td colspan="2">&nbsp;</td>
          <td class="grand_total">${$grand_total}</td>
          <td>&nbsp;</td>
        </tr>
        </tfoot>
      </table>
    </td>
  </tr>
</table>



{show_footer()}
