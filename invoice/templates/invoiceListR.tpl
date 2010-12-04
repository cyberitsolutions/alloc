<tr>
{if $_FORM["showInvoiceNumber"]}     <td>{$invoiceLink}</td>{/}
{if $_FORM["showInvoiceClient"]}     <td>{=$clientName}</td>{/}
{if $_FORM["showInvoiceName"]}       <td>{=$invoiceName}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$invoiceDateFrom}</td>{/}
{if $_FORM["showInvoiceDate"]}       <td class="nobr">{$invoiceDateTo}</td>{/}
{if $_FORM["showInvoiceStatus"]}     <td class="nobr">{$statii.$invoiceStatus}</td>{/}
{if $_FORM["showInvoiceAmount"]}     <td>{page::money($currencyTypeID,$iiAmountSum,"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td class="nobr" sorttable_customkey="{$status_label}">{$image}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($currencyTypeID,$amountPaidRejected,"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($currencyTypeID,$amountPaidPending,"%S%m")}</td>{/}
{if $_FORM["showInvoiceAmountPaid"]} <td>{page::money($currencyTypeID,$amountPaidApproved,"%S%m")}</td>{/}
</tr>

