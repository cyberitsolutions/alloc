{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Tagged Fund List
      <b> - {print count($tfListRows)} records</b>
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
          <td>
            <button type="submit" name="apply_filter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
  <tr>
    <td>
      {if $tfListRows}
      <table class="list sortable">
        <tr>
          <th>Account</th>
          <th>Description</th>
          <th width="1%">Enabled</th>
          <th class="right">Pending</th>
          <th class="right">Balance</th>
          <th></th>
        </tr>
        {foreach $tfListRows as $r}
        <tr>
          <td><a href="{$url_alloc_transactionList}tfID={$r.tfID}">{=$r.tfName}</a></td>
          <td>{=$r.tfComments}</td>
          <td>{$r.tfActive_label}</td>
          <td class="right nobr transaction-pending">{$r.tfBalancePending}</td>
          <td class="right nobr transaction-approved">{$r.tfBalance}</td>
          <td class="noprint right nobr" width="1%">{$r.nav_links}</td>
        </tr>
        {$grand_total += $r["total"]}
        {$grand_total_pending += $r["pending_total"]}
        {/}
        <tr>
          <td colspan="3">&nbsp;</td>
          <td class="grand_total right transaction-pending">{page::money(config::get_config_item("currency"),$grand_total_pending,"%s%m %c")}</td>
          <td class="grand_total right transaction-approved">{page::money(config::get_config_item("currency"),$grand_total,"%s%m %c")}</td>
          <td>&nbsp;</td>
        </tr>
      </table>

      {else}
        <b>No Accounts Found.</b>
      {/}
    </td>
  </tr>
</table>



{page::footer()}
