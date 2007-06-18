{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Client Details</th>
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
   
 
    {show_comments()}

    {show_attachments()}


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

{/}

{show_footer()}
