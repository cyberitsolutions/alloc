{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th colspan="5">Search Invoices</th>
  </tr>
  <tr>
    <td align="center" colspan="5">
      <form action="{url_alloc_searchInvoice}" method="post">
      <table class="filter" align="center" cellpadding="5" cellspacing="0">
        <tr>
          <td>ID</td>
          <td>Invoice Name</td>
          <td>Status</td>
          <td align="center">Dates (YYYY-MM-DD)</td>
          <td align="center">Invoice Number</td>
        </tr>
        <tr>
          <td><input type="text" size="5" name="invoiceItemID" value="{invoiceItemID}"></td>
          <td><input type="text" size="11" name="invoiceName" value="{invoiceName}"></td>
          <td><select name="invoiceItem_status"><option value="">All</option>{statusOptions}</select></td>
          <td>&nbsp; From &nbsp;&nbsp;&nbsp;
            <input type="text" size="11" name="dateOne" value="{dateOne}">
            <input type="button" onClick="dateOne.value='{today}'" value="Today">
            &nbsp;&nbsp;&nbsp; to &nbsp;&nbsp;&nbsp;
            <input type="text" size="11" name="dateTwo" value="{dateTwo}">
            <input type="button" onClick="dateTwo.value='{today}'" value="Today">
          </td>
          <td><input type="text" size="10" name="invoiceNum" value="{invoiceNum}"></td>
          <td rowspan="2"><input type="submit" name="search" value="SEARCH"></td>
        </tr>
      </table>
      </form>
    </td>
   </tr>
   <tr>
    <td><b>ID</b></td>
    <td><b>Invoice Number</b></td>
    <td><b>Name</b></td>
    <td><b>Invoice Date</b></td>
    <td><b>Status</b></td>
  </tr>
  {:startSearch templates/searchInvoiceR.tpl}
</table>


{:show_footer}
