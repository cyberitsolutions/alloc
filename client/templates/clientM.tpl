{show_header()}
{show_toolbar()}

{if check_optional_client_exists()}
{$first_div="hidden"}
{$sbs_link = $_POST["sbs_link"] or $sbs_link = $_GET["sbs_link"] or $sbs_link = "client"}
{get_side_by_side_links(array("client"=>"Main"
                             ,"reminders"=>"Reminders"
                             ,"comments"=>"Comments"
                             ,"attachments"=>"Attachments"
                             ,"projects"=>"Projects"
                             ,"invoices"=>"Invoices"
                             ,"sbsAll"=>"All"
                             ),$sbs_link)}
{/}

<div id="client" class="{$first_div}">
{$table_box}
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
  {$table_box}
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
  {$table_box}
    <tr>
      <th colspan="2">Projects</th>
      <th class="right"><a href="{$url_alloc_project}clientID={$client_clientID}">New Project</a></th>
    </tr>
    <tr>
      <td>Name</td>
      <td>Type</td>
      <td>Amount</td>
    </tr>
    {show_projects()}
  </table>
</div>

<div id="invoices" class="hidden">
  {$table_box}
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

{/}

{show_footer()}
