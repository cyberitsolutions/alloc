
      <form action="{$url_alloc_invoiceList}" method="post">
      <table class="filter" align="center" cellpadding="5" cellspacing="0">
        <tr>
          <td colspan="1">Client</td>
          <td>Invoice Name</td>
          <td>Invoice Status</td>
        </tr>
        <tr>
          <td colspan="1"><select name="clientID"><option value="">All</option>{$clientOptions}</select></td>
          <td><input type="text" size="11" name="invoiceName" value="{$invoiceName}"></td>
          <td><select name="invoiceStatus"><option value="">All</option>{$statusOptions}</select></td>
        </tr>
        <tr>
          <td align="center"></td>
          <td>Invoice Num</td>
          <td>Payment Status</td>
        </tr>
        <tr>
          <td>&nbsp; From &nbsp;&nbsp;&nbsp;
            {get_calendar("dateOne",$TPL["dateOne"])}
            &nbsp;&nbsp;&nbsp; to &nbsp;&nbsp;&nbsp;
            {get_calendar("dateTwo",$TPL["dateTwo"])}
          </td>
          <td><input type="text" size="11" name="invoiceNum" value="{$invoiceNum}"></td>
          <td><select name="invoiceStatusPayment"><option value="">All</option>{$statusPaymentOptions}</select></td>
          <td rowspan="2" align="right"><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
      </form>

