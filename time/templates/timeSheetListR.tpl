<tr>
{if $_FORM["showTimeSheetID"]}            <td>{$timeSheetID}</td>{/}
{if $_FORM["showProject"]}                <td>{=$projectName}</td>{/}
{if $_FORM["showProjectLink"]}            <td>{$projectLink}</td>{/}
{if $_FORM["showPerson"]}                 <td>{=$person}</td>{/}
{if $_FORM["showDateFrom"]}               <td>{$dateFrom}</td>{/}
{if $_FORM["showDateTo"]}                 <td>{$dateTo}</td>{/}
{if $_FORM["showStatus"]}                 <td>{if $dateRejected}<span class="bad" title="This timesheet has been rejected.">{/}{$status}{if $dateRejected}</span>{/}</td>{/}
{if $_FORM["showDuration"]}               <td>{$duration}</td>{/}
{if $_FORM["showAmount"]}                 <td align="right">{page::money($currencyTypeID,$amount,"%s%m %c")}</td>{/}
{if $_FORM["showCustomerBilledDollars"]}  <td align="right">{page::money($currencyTypeID,$customerBilledDollars,"%s%m %c")}</td>{/}
{if $_FORM["showTransactionsPos"]}        <td align="right">{$transactionsPos}</td>{/}
{if $_FORM["showTransactionsNeg"]}        <td align="right">{$transactionsNeg}</td>{/}
</tr>
 
