{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  obj = document.getElementById("timeSheetForm").clientID;
  id = obj.options[obj.selectedIndex].value;
  document.getElementById("projectDropdown").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
  url = '{$url_alloc_updateProjectListByClient}clientID='+id
  makeAjaxRequest(url,'updateProjectList',1)
\}

// Here's the callback function
function updateProjectList(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("projectDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
\}
</script>
<form action="{$url_alloc_timeSheet}" method="post" id="timeSheetForm">
<input type="hidden" name="timeSheetID" value="{$timeSheet_timeSheetID}">
<input type="hidden" name="timeSheet_personID" value="{$timeSheet_personID}">
<input type="hidden" name="projectID" value="{$projectID}">
<input type="hidden" name="timeSheet_dateFrom" value="{$timeSheet_dateFrom}">
<input type="hidden" name="timeSheet_dateTo" value="{$timeSheet_dateTo}">
<input type="hidden" name="timeSheet_status" value="{$timeSheet_status}">
<input type="hidden" name="timeSheet_dateSubmittedToManager" value="{$timeSheet_dateSubmittedToManager}">
<input type="hidden" name="timeSheet_dateSubmittedToAdmin" value="{$timeSheet_dateSubmittedToAdmin}">
<input type="hidden" name="timeSheet_approvedByManagerPersonID" value="{$timeSheet_approvedByManagerPersonID}">
<input type="hidden" name="timeSheet_approvedByAdminPersonID" value="{$timeSheet_approvedByAdminPersonID}">
<input type="hidden" name="timeSheet_recipient_tfID" value="{$recipient_tfID}">
<input type="hidden" name="timeSheet_invoiceDate" value="{$timeSheet_invoiceDate}">
<input type="hidden" name="taskID" value="{$taskID}" />

{$table_box}
  <tr>
    <th colspan="3">{get_help("timesheet_overview")}Time Sheet</th>  
    <th class="right" colspan="2">
      {if $TPL["timeSheet_timeSheetID"]}
        {$timeSheetPrintOptions = config::get_config_item("timeSheetPrintOptions")}
        {$timeSheetPrint = config::get_config_item("timeSheetPrint")}
        {foreach $timeSheetPrint as $value}
          <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&{$value}">{echo $timeSheetPrintOptions[$value]}</a>
        {/}
      {/}
    </th>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td width="20%" align="right">Created By:</td>
    <td>{$timeSheet_personName}</td>
    <td align="right">Client:</td>
	  <td>{$show_client_options}</td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td align="right">Amount:</td>
    <td>{$total_dollars}</td>
    <td align="right">Project:</td>
	  <td width="30%" class="nobr"><div id="projectDropdown" style="display:inline">{$show_project_options}</div></td>
  </tr>

  <tr>
	  <td>&nbsp;</td>
    <td align="right">Client Billing:</td>
    <td><nobr>{$total_customerBilledDollars}{$ex_gst}</nobr></td>
    <td align="right">Time Sheet Manager{$manager_plural}:</td>
    <td>{$managers}</td>
  </tr>

  <tr>
	  <td>&nbsp;</td>
    <td align="right">Units:</td>
    <td>{$total_units}</td>
    <td align="right"><nobr>Date Submitted to Manager{$manager_plural}:</nobr></td>
    <td>{$timeSheet_dateSubmittedToManager}</td>
  </tr>

	<tr>
	  <td>{get_help("which_tf_to_credit")}</td>
 	  <td align="right">TF:</td>
 	  <td align="left">{$recipient_tfID_name}</td>
    <td align="right">Approved by Manager:</td>
    <td>{$timeSheet_approvedByManagerPersonID_username}</td>
  </tr>

	<tr>
	  <td>&nbsp;</td>
    <td align="right">Period:</td>
    <td><nobr>{$period}</nobr></td>
    <td align="right">Date Submitted to Admin:</td>
    <td>{$timeSheet_dateSubmittedToAdmin}</td>
	</tr>

	<tr>
    {if config::get_config_item("paymentInsurancePercent")}
    <td>{get_help("payment_insurance")}</td>
    <td align="right">Payment Insurance:</td>
    <td>{$payment_insurance}</td>
    {else}
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    {/}
    <td align="right">Approved by Admin:</td> 
    <td>{$timeSheet_approvedByAdminPersonID_username}</td>
  </tr>

	<tr>
    <td>{get_help("timesheet_add_invoice")}</td>
    <td align="right" valign="top">Invoice:</td>
    <td class="nobr">{$attach_to_invoice_button}{$invoice_link}</td>
    <td class="right">{$amount_allocated_label}</td>
    <td>{$amount_allocated}</td>
  </tr>


  <tr>
    <td valign="top"></td>
    <td align="right" valign="top">{get_expand_link("billing_note_input","Billing Note ","billing_note_text")}</td>
    <td colspan="3" valign="top"><div id="billing_note_text">{echo text_to_html($TPL["timeSheet_billingNote"])}</div>
                                 <div style="display:none;" id="billing_note_input" class="nobr">
                                   <textarea rows="10" cols="70" wrap="virtual" name="timeSheet_billingNote">{$timeSheet_billingNote}</textarea>
                                 </div>
    </td>
  </tr>

  <tr>
    <td colspan="5"><br/><br/>
      <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" colspan="3">{$radio_email}</td>
        </tr>
        <tr>
          <td width="1%">{get_help("timesheet_buttons")}</td>
          <td align="center">{$timeSheet_ChangeStatusButton}<br/><br/>{$timeSheet_status_text}</td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>


{show_new_timeSheet("templates/timeSheetItemForm.tpl")}

{show_main_list()}

{show_transaction_list("templates/timeSheetTransactionListM.tpl")}


<br><br>
<br><br>&nbsp;

{show_footer()}


