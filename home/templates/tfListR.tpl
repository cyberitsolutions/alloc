<tr>
  <td><a href="{$url_alloc_transactionList}tfID={$tfID}">{=$tfName}</a></td>
  <td style="text-align:right" class="transaction-pending obfuscate">i {page::money(config::get_config_item("currency"),$pending_amount,"%s%m %c")}</td>
  <td style="text-align:right" class="transaction-approved obfuscate">{page::money(config::get_config_item("currency"),$tfBalance,"%s%m %c")}</td>
</tr>
