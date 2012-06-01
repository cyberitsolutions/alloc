    <form action="{$url_alloc_client}" method=post>
      <input type="hidden" name="clientID" value="{$client_clientID}">
      <table>
        <tr>
          <td colspan="3">
            <b>Client Name:</b> {=$client_clientName}<br>
            <b>Phone:</b> {=$client_clientPhoneOne}<br>
            <b>Fax:</b> {=$client_clientFaxOne}<br>
            <b>Status:</b> {=$client_clientStatus}<br>
            <b>Category:</b> {=$client_clientCategoryLabel}
          </td>
        </tr>
        <tr>
          <td valign="top">
            <b>Postal Address:</b><br>
            {$client_clientPostalAddress}
          </td>
          <td>&nbsp;</td>
          <td valign="top">
            <b>Street Address:</b><br>
            {$client_clientStreetAddress}
          </td>
        </tr>
      </table>
      <div style="text-align:center">
        <button type="submit" name="client_edit" value="1">Edit Client<i class="icon-edit"></i></button>
      </div>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
