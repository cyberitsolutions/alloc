<form action="{url_alloc_client}" method=post>
  <input type="hidden" name="clientContactID" value="{client_clientContactID}">
  <input type="hidden" name="clientID" value="{client_clientID}">
  {table_box}
    <tr>
      <th colspan="7">{client_clientTitle}</th>
    </tr>
    <tr>
      <td>Name</td> <td><input type="text" name="clientContactName" value="{client_clientContactName}"></td>
      <td>Phone Number</td> <td><input type="text" name="clientContactPhone" value="{client_clientContactPhone}"></td>
      <td>Info</td>
      <td rowspan="4"><textarea name="clientContactOther" cols="50" rows="6" wrap="virtual">{client_clientContactOther}</textarea></td>
    </tr>
    <tr>
      <td>Street Address</td> <td><input type="text" name="clientContactStreetAddress" value="{client_clientContactStreetAddress}"></td>
      <td>Fax Number</td> <td><input type="text" name="clientContactFax" value="{client_clientContactFax}"></td>
      <td></td><td></td>
    </tr>
    <tr>
      <td>Suburb</td> <td><input type="text" name="clientContactSuburb" value="{client_clientContactSuburb}"></td>
      <td>Mobile Number</td> <td><input type="text" name="clientContactMobile" value="{client_clientContactMobile}"></td>
      <td></td><td></td>
    </tr>
    <tr>
      <td>Postcode</td> <td><input type="text" name="clientContactPostcode" value="{client_clientContactPostcode}"></td>
      <td>Email Address</td> <td><input type="text" name="clientContactEmail" value="{client_clientContactEmail}"></td>
      <td></td><td></td>
    </tr>
    <tr>
      <td>State</td> <td><input type="text" name="clientContactState" value="{client_clientContactState}"></td>
      <td></td><td></td>
      <td></td><td></td>
    </tr>
    <tr>
      <td colspan="7" align="center">{clientContactItem_buttons}</td>
    </tr>
  </table>
  </form>
