{if $invoiceListRows}
<table class="list sortable">
{if $_FORM["showHeader"]}

<tr>
{if $_FORM["showInvoiceNumber"]}       <th>Invoice Number</th>{/}
{if $_FORM["showInvoiceClient"]}       <th>Client</th>{/}
{if $_FORM["showInvoiceName"]}         <th>Name</th>{/}
{if $_FORM["showInvoiceDate"]}         <th>From</th>{/}
{if $_FORM["showInvoiceDate"]}         <th>To</th>{/}
{if $_FORM["showInvoiceStatus"]}       <th>Status</th>{/}
{if $_FORM["showInvoiceAmount"]}       <th>Amount</th>{/}
{if $_FORM["showInvoiceAmountPaid"]}   <th>&nbsp;</th>{/}
{if $_FORM["showInvoiceAmountPaid"]}   <th>Rejected</th>{/}
{if $_FORM["showInvoiceAmountPaid"]}   <th>Pending</th>{/}
{if $_FORM["showInvoiceAmountPaid"]}   <th>Approved</th>{/}
<th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
</tr>
{/}

{foreach $invoiceListRows as $r}
<tr>
{if $_FORM["showInvoiceNumber"]}     <td>{$r.invoiceLink}</td>{/}
{if $_FORM["showInvoiceClient"]}     <td>{=$r.clientName}</td>{/}
{if $_FORM["showInvoiceName"]}       <td>{=$r.invoiceName}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$r.invoiceDateFrom}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$r.invoiceDateTo}</td>{/}
{if $_FORM["showInvoiceStatus"]}     <td class="nobr">{echo $r["statii"][$r["invoiceStatus"]]}</td>{/}
{if $_FORM["showInvoiceAmount"]}     <td>{page::money($r["currencyTypeID"],$r["iiAmountSum"],"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td class="nobr" sorttable_customkey="{$r.status_label}">{$r.image}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($r["currencyTypeID"],$r["amountPaidRejected"],"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($r["currencyTypeID"],$r["amountPaidPending"],"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($r["currencyTypeID"],$r["amountPaidApproved"],"%S%m")}</td>{/}
  <td width="1%">
    {page::star("invoice",$r["invoiceID"])}
  </td>
</tr>
{/}

</table>
{/}
