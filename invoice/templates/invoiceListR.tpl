<tr>
{if $_FORM["showInvoiceNumber"]}     <td>{$invoiceLink}</td>{/}
{if $_FORM["showInvoiceClient"]}     <td>{=$clientName}</td>{/}
{if $_FORM["showInvoiceName"]}       <td>{=$invoiceName}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$invoiceDateFrom}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$invoiceDateTo}</td>{/}
{if $_FORM["showInvoiceStatus"]}     <td class="nobr">{$statii.$invoiceStatus}</td>{/}
{if $_FORM["showInvoiceAmount"]}     <td>{echo $currency.sprintf("%0.2f",$iiAmountSum)}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td class="nobr" sorttable_customkey="{$status_label}">{$image}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{echo $currency.sprintf("%0.2f",$amountPaidRejected)}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{echo $currency.sprintf("%0.2f",$amountPaidPending)}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{echo $currency.sprintf("%0.2f",$amountPaidApproved)}</td>{/}
</tr>

