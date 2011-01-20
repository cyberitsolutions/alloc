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
          <a href="{$url_alloc_reminderAdd}step=3&parentType=client&parentID={$client_clientID}&returnToParent=client">Add Reminder</a>
        </span>
      </th>
    </tr>
    <tr>
      <td>
        <table class="sortable list">
          <tr>
            <th>Recipient</th>
            <th>Date / Time</th>
            <th>Subject</th>
            <th>Repeat</th>
          </tr>
          {show_reminders("../reminder/templates/reminderR.tpl")}
        </table>
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
        {echo project::get_list(array("showHeader"=>true
                                     ,"showProjectLink"=>true
                                     ,"showClient"=>true
                                     ,"showProjectType"=>true
                                     ,"showProjectStatus"=>true
                                     ,"showNavLinks"=>true
                                     ,"return"=>"html"
                                     ,"clientID"=>$client_clientID)
                                );}
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
