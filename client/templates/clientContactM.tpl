  {$table_box}
    <tr>
      <th>Client Contacts</th>
      <th class="right">{print_expand_link("id_new_client_contact")}</th>
    </tr>
    <tr>
      <td colspan="2">
        <form action="{$url_alloc_client}" method="post">
        <input type="hidden" name="clientContactID" value="{$clientContact_clientContactID}">
        <input type="hidden" name="clientID" value="{$clientContact_clientID}">
        
        <div class="{$class_new_client_contact}" id="id_new_client_contact">
        <table width="100%">
          <tr>
            <td>Name</td> <td><input type="text" name="clientContactName" value="{$clientContact_clientContactName}"></td>
            <td>Email</td> <td><input type="text" name="clientContactEmail" value="{$clientContact_clientContactEmail}"></td>
            <td>Info</td>
            <td rowspan="6" valign="top" align="right">{get_textarea("clientContactOther",$TPL["clientContact_clientContactOther"],array("height"=>"medium"))}<br>
                                         Primary Contact <input type="checkbox" name="clientPrimaryContactID" value="1"{$clientPrimaryContactID_checked}> {$clientContactItem_buttons}

            </td>
          </tr>
          <tr>
            <td class="nobr">Address</td> <td><input type="text" name="clientContactStreetAddress" value="{$clientContact_clientContactStreetAddress}"></td>
            <td>Phone</td> <td><input type="text" name="clientContactPhone" value="{$clientContact_clientContactPhone}"></td>
            <td></td><td></td>
          </tr>
          <tr>
            <td>Suburb</td> <td><input type="text" name="clientContactSuburb" value="{$clientContact_clientContactSuburb}"></td>
            <td>Mobile</td> <td><input type="text" name="clientContactMobile" value="{$clientContact_clientContactMobile}"></td>
            <td></td><td></td>
          </tr>
          <tr>
            <td>State</td> <td><input type="text" name="clientContactState" value="{$clientContact_clientContactState}"></td>
            <td>Fax</td> <td><input type="text" name="clientContactFax" value="{$clientContact_clientContactFax}"></td>
            <td></td><td></td>
          </tr>
          <tr>
            <td>Postcode</td> <td><input type="text" name="clientContactPostcode" value="{$clientContact_clientContactPostcode}"></td>
            <td class="nobr">Country</td><td><input type="text" name="clientContactCountry" value="{$clientContact_clientContactCountry}"></td>
            <td></td><td align="right"></td>
          </tr>
          <tr>
            <td colspan="6">&nbsp;</td>
          </tr>
        </table>
        </div>

        </form>

      </td>
    </tr>
    <tr>
      <td colspan="2">
        {$clientContacts}
      </td>
    </tr>
  </table>
