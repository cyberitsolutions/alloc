{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
$(document).ready(function() {
  {if !$client_clientID}
    toggle_view_edit();
    $('#clientName').focus();
  {else}
    $('#editClient').focus();
  {/}
});
</script>

{if check_optional_client_exists()}
{$first_div="hidden"}
{page::side_by_side_links(array("client"=>"Main"
                               ,"reminders"=>"Reminders"
                               ,"comments"=>"Comments"
                               ,"attachments"=>"Attachments"
                               ,"projects"=>"Projects"
                               ,"invoices"=>"Invoices"
                               ,"sales"=>"Sales"
                               ,"sbsAll"=>"All")
                          ,$url_alloc_client."clientID=".$client_clientID)}
{/}

<!-- need to merge this style back into the stylesheets -->
<style>
.task_pane {
  min-width:400px;
  width:47%;
  float:left;
  margin:0px 12px;
  vertical-align:top;
}
</style>


<div id="client" class="{$first_div}">
<form action="{$url_alloc_client}" method=post>
<input type="hidden" name="clientID" value="{$client_clientID}">

<table class="box view">
  <tr>
    <th class="header">{$clientSelfLink}
      <span>{page::star("client",$client_clientID)}</span>
    </th>
  </tr>
  <tr>
    <td valign="top">
      <div class="task_pane">
        <h6>Client Name{page::mandatory($client_clientName)}</h6>
        <h2 style="margin-bottom:0px; display:inline;">{$client_clientID} {=$client_clientName}</h2>
        &nbsp;&nbsp;&nbsp;{$client_clientStatus} {=$client_clientCategoryLabel}
        {if $client_clientPostalAddress} 
          <h6>Postal Address</h6>
          {$client_clientPostalAddress}
        {/}
      </div>
      <div class="task_pane">
        <div class="enclose">
          <h6>Phone Number<div>Fax Number</div></h6>
          <div style="float:left; width:47%;">
            {=$client_clientPhoneOne}
          </div>
          <div style="float:right; width:50%;">
            {=$client_clientFaxOne}
          </div>
        </div>
        {if $client_clientStreetAddress} 
          <h6>Street Address</h6>
          {$client_clientStreetAddress}
        {/}
      </div>
    </td>
  </tr>
  <tr>
    <td align="center" class="padded">
      <div style="margin:20px">
        <button type="button" id="editClient" value="1" onClick="return toggle_view_edit();">Edit Client<i class="icon-edit"></i></button>
      </div>
    </td>
  </tr>
</table>

<table class="box edit">
  <tr>
    <th class="header">{$clientSelfLink}
      <span></span>
    </th>
  </tr>
  <tr>
    <td>
      <div class="task_pane">
        <h6>Client Name{page::mandatory($client_clientName)}</h6>
        <div style="width:100%" class="">
          <input type="text" size="43" id="clientName" name="clientName" value="{$client_clientName}" tabindex="1">
          <select name="clientStatus" tabindex="2">{$clientStatusOptions}</select>
          <select name="clientCategory" tabindex="3">{$clientCategoryOptions}</select>
        </div>
        <h6>Postal Address</h6>
        <table border="0" cellspacing=0 cellpadding=5 width="100%">
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
      </div>
      <div class="task_pane">
        <div class="enclose">
          <h6>Phone Number<div>Fax Number</div></h6>
          <div style="float:left; width:47%;">
            <input type="text" name="clientPhoneOne" value="{$client_clientPhoneOne}" tabindex="3">
          </div>
          <div style="float:right; width:50%;">
            <input type="text" name="clientFaxOne" value="{$client_clientFaxOne}" tabindex="4">
          </div>
        </div>
        <h6>Street Address</h6>
        <table border="0" cellspacing=0 cellpadding=5 width="100%">
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
      </div>
    </td>
  </tr>
  <tr>
    <td align="center" class="padded">
      <div style="margin:20px">
        {if $client_clientID}
        <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        {/}
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        {if $client_clientID}
        <br><br>
        &nbsp;&nbsp;<a href="" onClick="return toggle_view_edit(true);">Cancel edit</a>
        {/}
      </div>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{if check_optional_client_exists()}
  {show_client_contacts()}
{/}

</div>

{if check_optional_client_exists()}
<div id="reminders">
  <table class="box">
    <tr>
      <th class="header">Reminders
        <span>
          <a href="{$url_alloc_reminder}step=3&parentType=client&parentID={$client_clientID}&returnToParent=client">Add Reminder</a>
        </span>
      </th>
    </tr>
    <tr>
      <td>
      {reminder::get_list_html("client",$client_clientID)}
      </td>
    </tr>
  </table>
</div>
  
<div id="comments">
  {show_comments()}
</div>

<div id="attachments">
  {show_attachments()}
</div>

<div id="projects">
  <table class="box">
    <tr>
      <th class="header">Projects
        <span>
          <a href="{$url_alloc_project}clientID={$client_clientID}">New Project</a>
        </span>
      </th>
    </tr>
    <tr>
      <td>
        {if $projectListRows}
        <table class="list sortable">
          <tr>
            <th>Project</th>
            <th>Nick</th>
            <th>Client</th>
            <th>Type</th>
            <th>Status</th>
            <th class="noprint">&nbsp;</th>
          </tr>
          {foreach $projectListRows as $r}
          <tr>
            <td>{$r.projectLink}</td>
            <td>{=$r.projectShortName}</td>
            <td>{=$r.clientName}</td>
            <td>{=$r.projectType}</td>
            <td>{=$r.projectStatus}</td>
            <td class="noprint" align="right">{$r.navLinks}</td>
          </tr>
          {/}
        </table>
        {else}
          <b>No Projects Found.</b>
        {/}
      </td>
    </tr>
  </table>
</div>

<div id="invoices">
  <table class="box">
    <tr>
      <th class="header">Invoices
        <span>
          {$invoice_links}
        </span>
      </th>
    </tr>
    <tr>
      <td>
        {show_invoices()}
      </td>
    </tr>
  </table>
</div>

<div id="sales">
<table class="box">
  <tr>
    <th class="header">Sales
      <span>
        <a href="{$url_alloc_productSale}clientID={$client_clientID}">New Sale</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {$productSaleRows = productSale::get_list(array("clientID"=>$client_clientID))}
      {echo productSale::get_list_html($productSaleRows)}
    </td>
  </tr>
</table>
</div>

{/}

{page::footer()}
