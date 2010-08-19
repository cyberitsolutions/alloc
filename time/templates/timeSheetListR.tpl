<tr>
{if $_FORM["showTimeSheetID"]}            <td>{$timeSheetID}</td>{/}
{if $_FORM["showProject"]}                <td>{=$projectName}</td>{/}
{if $_FORM["showProjectLink"]}            <td>{$projectLink}</td>{/}
{if $_FORM["showPerson"]}                 <td>{=$person}</td>{/}
{if $_FORM["showDateFrom"]}               <td>{$dateFrom}</td>{/}
{if $_FORM["showDateTo"]}                 <td>{$dateTo}</td>{/}
{if $_FORM["showStatus"]}                 <td>{$status}</td>{/}
{if $_FORM["showDuration"]}               <td>{$duration}</td>{/}
{if $_FORM["showAmount"]}                 <td align="right">{echo sprintf("$%0.2f",$amount)}</td>{/}
{if $_FORM["showCustomerBilledDollars"]}  <td align="right">{echo sprintf("$%0.2f",$customerBilledDollars)}</td>{/}
{if $_FORM["showTransactionsPos"]}        <td align="right">{echo sprintf("$%0.2f",$transactionsPos)}</td>{/}
{if $_FORM["showTransactionsNeg"]}        <td align="right">{echo sprintf("$%0.2f",$transactionsNeg)}</td>{/}
</tr>
 
