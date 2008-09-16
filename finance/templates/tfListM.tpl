{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th colspan="3">TF List</th>
    <th class="right">{if have_entity_perm("tf", PERM_CREATE, $current_user, true)}<a href="{$url_alloc_tf}">New Tagged Fund</a>{/}</th>
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
      <table class="list sortable">
        <tr>
          <th>TF Name</th>
          <th>Description</th>
          <th class="right">Balance</th>
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



{page::footer()}
