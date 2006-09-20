{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Client Details</th>
    <th class="right"><nobr><a href="{url_alloc_clientList}">Return to Client List</a></nobr></th>
  </tr>
  <tr>
    <td>
      {:show_client_details_edit templates/clientDetailsEdit.tpl}
      {:show_client_details templates/clientDetails.tpl}
    </td>
  </tr>
</table>

{optional:client_exists}

    {:show_client_contacts templates/clientContactR.tpl}

    {table_box}
      <tr>
        <th>Reminders</th>
        <th class="right" colspan="3"><a href="{url_alloc_reminderAdd}step=3&parentType=client&parentID={client_clientID}&returnToParent=t">Add Reminder</a></th>
      </tr>
      <tr>
        <td>Recipient</td>
        <td>Date / Time</td>
        <td>Subject</td>
        <td>Repeat</td>
      </tr>
      {:show_reminders ../notification/templates/reminderR.tpl}
    </table>
   
{table_box}
  <tr>
    <th colspan="2">Comments</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{url_alloc_client}" method="post">
      <table width="100%">
        <tr>
          <td>
            <input type="hidden" name="clientID" value="{client_clientID}">
            <textarea name="clientComment" cols="85" rows="4" wrap="virtual">{client_clientComment}</textarea>&nbsp;
          </td>
          <td align="right" valign="top">
            {client_clientComment_buttons}
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  {:show_comments templates/clientCommentR.tpl}
</table>
 

{:show_attachments}

    {table_box}
      <tr>
        <th colspan="3">Projects</th>
      </tr>
      <tr>
        <td>Name</td>
        <td>Type</td>
        <td>Amount</td>
      </tr>
      {:show_projects}
    </table>

{/optional}

{:show_footer}
