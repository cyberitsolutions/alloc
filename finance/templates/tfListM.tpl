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
          <td><label for="owner">Owner</label> <input type="checkbox" id="owner" name="owner"{$owner_checked}>&nbsp;&nbsp;</td>
	        <td><label for="showall">Show All</label> <input type="checkbox" id="showall" name="showall"{$showall_checked}></td>
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
          <th width="1%">Enabled</th>
          <th class="right">Balance</th>
          <th></th>
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
