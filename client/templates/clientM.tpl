{page::header()}
{page::toolbar()}

{if check_optional_client_exists()}
{$first_div="hidden"}
{$sbs_link = $_POST["sbs_link"] or $sbs_link = $_GET["sbs_link"] or $sbs_link = "client"}
{page::side_by_side_links(array("client"=>"Main"
                               ,"reminders"=>"Reminders"
                               ,"comments"=>"Comments"
                               ,"attachments"=>"Attachments"
                               ,"projects"=>"Projects"
                               ,"invoices"=>"Invoices"
                               ,"sales"=>"Sales"
                               ,"sbsAll"=>"All")
                          ,$sbs_link
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
<div id="reminders" class="hidden">
  <table class="box">
    <tr>
      <th>Reminders</th>
      <th class="right" colspan="3"><a href="{$url_alloc_reminderAdd}step=3&parentType=client&parentID={$client_clientID}&returnToParent=client">Add Reminder</a></th>
    </tr>
    <tr>
      <td>Recipient</td>
      <td>Date / Time</td>
      <td>Subject</td>
      <td>Repeat</td>
    </tr>
    {show_reminders("../reminder/templates/reminderR.tpl")}
  </table>
</div>
  
<div id="comments" class="hidden">
  {show_comments()}
</div>

<div id="attachments" class="hidden">
  {show_attachments()}
</div>

<div id="projects" class="hidden">
  <table class="box">
    <tr>
      <th colspan="2">Projects</th>
      <th class="right"><a href="{$url_alloc_project}clientID={$client_clientID}">New Project</a></th>
    </tr>
    <tr>
      <td colspan="3">
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

<div id="invoices" class="hidden">
  <table class="box">
    <tr>
      <th>Invoices</th>
      <th class="right">{$invoice_links}</th>
    </tr>
     <tr>
      <td colspan="2">
        {show_invoices()}
      </td>
    </tr>
  </table>
</div>

<div id="sales" class="hidden">
<table class="box">
  <tr>
    <th>Sales</th>
    <th class="right"><a href="{$url_alloc_productSale}clientID={$client_clientID}">New Sale</a></th>
  </tr>
  <tr>
    <td colspan="2">
      {echo productSale::get_list(array("clientID"=>$client_clientID))}
    </td>
  </tr>
</table>
</div>

{/}

{page::footer()}
