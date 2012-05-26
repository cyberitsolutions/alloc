
      <form action="{$url_alloc_invoiceList}" method="get">
      <table class="filter corner" align="center" cellpadding="5" cellspacing="0">
        <tr>
          <td colspan="2">Client</td>
          <td>Invoice Name</td>
          <td>Invoice Status</td>
        </tr>
        <tr>
          <td colspan="2"><select name="clientID"><option value="">All</option>{$clientOptions}</select></td>
          <td><input type="text" size="11" name="invoiceName" value="{$invoiceName}"></td>
          <td><select name="invoiceStatus"><option value="">All</option>{$statusOptions}</select></td>
        </tr>
        <tr>
          <td>From</td>
          <td>To</td>
          <td>Invoice Num</td>
          <td>Payment Status</td>
        </tr>
        <tr>
          <td>{page::calendar("dateOne",$dateOne)}</td>
          <td>{page::calendar("dateTwo",$dateTwo)}</td>
          <td><input type="text" size="11" name="invoiceNum" value="{$invoiceNum}"></td>
          <td><select name="invoiceStatusPayment"><option value="">All</option>{$statusPaymentOptions}</select></td>
        </tr>
        <tr>
          <td colspan="4">Note: Only user accounts with Financial Administrator privileges can see the full totals of invoices.
          <br>{$status_legend}
          <input type="submit" name="applyFilter" value="Filter" style="margin-left:5px; margin-top:5px;"></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

