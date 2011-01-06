{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">TF List
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        {if have_entity_perm("tf", PERM_CREATE, $current_user, true)}
          <a href="{$url_alloc_tf}">New Tagged Fund</a>
        {/}
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      <form action="{$url_alloc_tfList}" method="get">
      <table class="filter corner" align="center">
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
    <td>
      <table class="list sortable">
        <tr>
          <th>TF Name</th>
          <th>Description</th>
          <th width="1%">Enabled</th>
          <th class="right">Balance</th>
          <th></th>
        </tr>
        {show_tf_list("templates/tfListR.tpl")}
        <tfoot>
        <tr>
          <td colspan="3">&nbsp;</td>
          <td class="grand_total right">{page::money(config::get_config_item("currency"),$grand_total,"%s%m %c")}</td>
          <td>&nbsp;</td>
        </tr>
        </tfoot>
      </table>
    </td>
  </tr>
</table>



{page::footer()}
