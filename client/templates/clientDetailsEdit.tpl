    <form action="{$url_alloc_client}" method=post>
      <input type="hidden" name="clientID" value="{$client_clientID}">
      <table>
        <tr>
          <td colspan="3" width="100%">
            <table border="0" cellspacing=0 cellpadding=5 width="100%">
              <tr>
                <td class="nobr"><b>Company Name</b></td>
                <td colspan="2"><input type="text" size="43" name="clientName" value="{$client_clientName}" tabindex="1"></td>
                <td><select name="clientStatus" tabindex="2">{$clientStatusOptions}</select></td>
              </tr>
              <tr>
                <td><b>Phone</b></td>
                <td><input type="text" name="clientPhoneOne" value="{$client_clientPhoneOne}" tabindex="3"></td>
                <td><b>Fax</b></td>
                <td><input type="text" name="clientFaxOne" value="{$client_clientFaxOne}" tabindex="4"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" cellspacing=0 cellpadding=5 width="100%">
              <tr>
                <td colspan="2"><strong>Postal Address</strong></td>
              </tr>
              <tr>
                <td>Address</td>
                <td><input type="text" name="clientStreetAddressOne" value="{$client_clientStreetAddressOne}" size="25" tabindex="5"></td>
              </tr>
              <tr>
                <td>Suburb</td>
                <td><input type="text" name="clientSuburbOne" value="{$client_clientSuburbOne}" size="25" tabindex="6"></td>
              </tr>
              <tr>
                <td>State</td>
                <td><input type="text" name="clientStateOne" value="{$client_clientStateOne}" size="25" tabindex="7"></td>
              </tr>
              <tr>
                <td>Postcode</td>
                <td><input type="text" name="clientPostcodeOne" value="{$client_clientPostcodeOne}" size="25" tabindex="8"></td>
              </tr>
              <tr>
                <td>Country</td>
                <td><input type="text" name="clientCountryOne" value="{$client_clientCountryOne}" size="25" tabindex="9"></td>
              </tr>
            </table>
          </td>
          <td>
            <table border="0" cellspacing=0 cellpadding=5 width="100%">
              <tr>
                <td colspan="2"><strong>Street Address</strong></td>
              </tr>
              <tr>
                <td>Address</td>
                <td><input type="text" name="clientStreetAddressTwo" value="{$client_clientStreetAddressTwo}" size="25" tabindex="10"></td>
              </tr>
              <tr>
                <td>Suburb</td>
                <td><input type="text" name="clientSuburbTwo" value="{$client_clientSuburbTwo}" size="25" tabindex="11"></td>
              </tr>
              <tr>
                <td>State</td>
                <td><input type="text" name="clientStateTwo" value="{$client_clientStateTwo}" size="25" tabindex="12"></td>
              </tr>
              <tr>
                <td>Postcode</td>
                <td><input type="text" name="clientPostcodeTwo" value="{$client_clientPostcodeTwo}" size="25" tabindex="13"></td>
              </tr>
              <tr>
                <td>Country</td>
                <td><input type="text" name="clientCountryTwo" value="{$client_clientCountryTwo}" size="25" tabindex="14"></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>{$createGeneralSupportProject}</td>
          <td colspan="2" align="right" class="nobr">{$clientDetails_buttons}</td>
        </tr>
      </table>
    </form>
