  <table class="box">
    <tr>
      <th class="header">Client Contacts
        <span>
          {page::expand_link("id_new_client_contact","New Client Contact")}
        </span>
      </th>
    </tr>
    <tr>
      <td colspan="2">
        <form action="{$url_alloc_client}" method="post">
        <input type="hidden" name="clientContactID" value="{$clientContact_clientContactID}">
        <input type="hidden" name="clientID" value="{$clientContact_clientID}">
        
        <div class="{$class_new_client_contact}" id="id_new_client_contact">
        <table width="100%">
          <tr>
            <td width="1%">Name</td> 
            <td width="1%"><input type="text" name="clientContactName" value="{$clientContact_clientContactName}"></td>
            <td width="1%">Email</td>
            <td width="1%"><input type="text" name="clientContactEmail" value="{$clientContact_clientContactEmail}"></td>
            <td>Info</td>
            <td rowspan="5" class="top right">{page::textarea("clientContactOther",$clientContact_clientContactOther,array("height"=>"medium","width"=>"100%"))}</td>
          </tr>
          <tr>
            <td>Address</td>
            <td><input type="text" name="clientContactStreetAddress" value="{$clientContact_clientContactStreetAddress}"></td>
            <td>Phone</td>
            <td><input type="text" name="clientContactPhone" value="{$clientContact_clientContactPhone}"></td>
          </tr>
          <tr>
            <td>Suburb</td>
            <td><input type="text" name="clientContactSuburb" value="{$clientContact_clientContactSuburb}"></td>
            <td>Mobile</td> 
            <td><input type="text" name="clientContactMobile" value="{$clientContact_clientContactMobile}"></td>
          </tr>
          <tr>
            <td>State</td>
            <td><input type="text" name="clientContactState" value="{$clientContact_clientContactState}"></td>
            <td>Fax</td>
            <td><input type="text" name="clientContactFax" value="{$clientContact_clientContactFax}"></td>
          </tr>
          <tr>
            <td>Postcode</td>
            <td><input type="text" name="clientContactPostcode" value="{$clientContact_clientContactPostcode}"></td>
            <td class="nobr">Country</td>
            <td><input type="text" name="clientContactCountry" value="{$clientContact_clientContactCountry}"></td>
          </tr>
          <tr>
            <td colspan="6" class="right">
             <label for="cca">Enabled</label> <input id="cca" type="checkbox" name="clientContactActive" value="1"{$clientContactActive_checked}>&nbsp;&nbsp;
             <label for="pcc">Primary Contact</label> <input id="pcc" type="checkbox" name="primaryContact" value="1"{$primaryContact_checked}>
             <button type="submit" name="clientContact_save" value="1" class="save_button">Save Client Contact<i class="icon-ok-sign"></i></button>

            </td>
          </tr>
        </table>
        </div>

        <input type="hidden" name="sessID" value="{$sessID}">
        </form>

      </td>
    </tr>
    <tr>
      <td colspan="2">
        {$clientContacts}
      </td>
    </tr>
  </table>
