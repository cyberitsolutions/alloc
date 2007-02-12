    <form action="{$url_alloc_client}" method=post>
      <input type="hidden" name="clientID" value="{$client_clientID}">
      <table>
        <tr>
          <td colspan="3">
            <b>Company Name:</b> {$client_clientName}<br/>
            <b>Phone:</b> {$client_clientPhoneOne}<br/>
            <b>Fax:</b> {$client_clientFaxOne}</br>
            <b>Status:</b>{$client_clientStatus}
          </td>
        </tr>
        <tr>
          <td>
            <b>Postal Address:</b><br>
            {$client_clientPostalAddress}
          </td>
          <td>&nbsp;</td>
          <td>
            <b>Street Address:</b><br>
            {$client_clientStreetAddress}
          </td>
        </tr>
      </table>
      <div style="text-align:center">
        <input type="submit" name="client_edit" value="Edit Client Details">
      </div>
    </form>
