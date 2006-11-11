    <form action="{$url_alloc_client}" method=post>
      <input type="hidden" name="clientID" value="{$client_clientID}">
      <table>
        <tr>
          <td colspan="3">
            <b>Company Name:</b> {$client_clientName} ({$client_clientStatus})<br>
            <b>Phone:</b> {$client_clientPhoneOne}<br>
            <b>Fax:</b> {$client_clientFaxOne}
          </td>
        </tr>
        <tr>
          <td width="48%">
            <b>Postal Address:</b><br>
            {$client_clientPostalAddress}
          </td>
          <td width="4%">&nbsp;</td>
          <td width="48%">
            <b>Street Address:</b><br>
            {$client_clientStreetAddress}
          </td>
        </tr>
        <tr>
          <td colspan="3">
            <div>
              <input type="submit" name="client_edit" value="Edit Client Details">
            </div>
          </td>
        </tr>
      </table>
    </form>
