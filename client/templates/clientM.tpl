{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Client Details</th>
    <th class="right"><nobr><a href="{$url_alloc_clientList}">Return to Client List</a></nobr></th>
  </tr>
  <tr>
    <td>
      {show_client_details_edit("templates/clientDetailsEdit.tpl")}
      {show_client_details("templates/clientDetails.tpl")}
    </td>
  </tr>
</table>

{if check_optional_client_exists()}

    {show_client_contacts("templates/clientContactR.tpl")}

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
        <th colspan="3">Projects</th>
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
