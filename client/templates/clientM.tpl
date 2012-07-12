{page::header()}
{page::toolbar()}

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

<div id="client" class="{$first_div}">
<table class="box">
  <tr>
    <th>Client: {$clientSelfLink}</th>
  </tr>
  <tr>
    <td>
      {show_client_details_edit("templates/clientDetailsEdit.tpl")}
      {show_client_details("templates/clientDetails.tpl")}
    </td>
  </tr>
</table>

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
      {echo productSale::get_list(array("clientID"=>$client_clientID))}
    </td>
  </tr>
</table>
</div>

{/}

{page::footer()}
