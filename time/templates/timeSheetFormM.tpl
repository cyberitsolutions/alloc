{show_header()}
{show_toolbar()}
<form action="{$url_alloc_timeSheet}" method=post>
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

{$table_box}
  <tr>
    <th colspan="3">{get_help("timesheet_overview")}Time Sheet</th>  
    <th class="right" colspan="2">
      {if $TPL["timeSheet_timeSheetID"]}
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=items">Default</a>
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=items&printDesc=1">+</a> |
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=units">Units</a>
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=units&printDesc=1">+</a> |
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=money">Invoice</a>
      <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&timeSheetPrintMode=money&printDesc=1">+</a>
      <img src="{$url_alloc_images}pdf_icon.png">
      {/}
    </th>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td width="20%" align="right">Created By:</td>
    <td>{$timeSheet_personName}</td>
    <td align="right">Client:</td>
	  <td>{$client_link}</td>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td align="right">Amount:</td>
    <td>{$total_dollars}</td>
    <td align="right">Project:</td>
	  <td width="30%">{$show_project_options}</td>
  </tr>

  <tr>
	  <td>&nbsp;</td>
    <td align="right">Customer Billing:</td>
    <td><nobr>{$total_customerBilledDollars}{$ex_gst}</nobr></td>
    <td align="right">Project Cost Centre:</td>
	  <td>{$cost_centre_link}</td>
  </tr>

	<tr>
	  <td>&nbsp;</td>
    <td align="right">Units:</td>
    <td>{$total_units}</td>
    <td align="right"><nobr>Date Submitted to Manager:</nobr></td>
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
    {if config::get_config_item("paymentInsurancePercent")}
    <td>{get_help("payment_insurance")}</td>
    <td align="right">Payment Insurance:</td>
    <td>{$payment_insurance}</td>
    {else}
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    {/}
    <td align="right">Date Submitted to Admin:</td>
    <td>{$timeSheet_dateSubmittedToAdmin}</td>
  </tr>

	<tr>
	  <td>&nbsp;</td>
    <td align="right">Period:</td>
    <td><nobr>{$period}</nobr></td>
    <td align="right">Approved by Admin:</td> 
    <td>{$timeSheet_approvedByAdminPersonID_username}</td>
	</tr>

  <tr>
    <td valign="top">
          {if $TPL["timeSheet_status"] != "finished"} 
          <div id="shrink_ts_note" style="display:none;">
            <img src="../images/shrink.gif"
                   onMouseUp="document.getElementById('ts_note').style.height='22px';
                              document.getElementById('shrink_ts_note').style.display='none'
                              document.getElementById('grow_ts_note').style.display='inline'" alt="Restore">
          </div>
          <div id="grow_ts_note">
            <img src="../images/grow.gif"
                   onMouseUp="document.getElementById('ts_note').style.height='150px';
                              document.getElementById('grow_ts_note').style.display='none'
                              document.getElementById('shrink_ts_note').style.display='inline'" alt="Expand">
          </div>
          {/}


    </td>
    <td align="right" valign="top">Billing Note:</td>
    <td colspan="3" valign="top">

          {if $TPL["timeSheet_status"] != "finished"} 
          <textarea rows="3" cols="70" wrap="virtual" id="ts_note" style="height:22px;" name="timeSheet_billingNote"
                    onFocus="document.getElementById('ts_note').style.height='150px';
                             document.getElementById('grow_ts_note').style.display='none'
                             document.getElementById('shrink_ts_note').style.display='inline'">{$timeSheet_billingNote}</textarea>
          {else}
            <input type="hidden" name="timeSheet_billingNote" value="{$timeSheet_billingNote}">
            {$timeSheet_billingNote}
          {/}
</td>


  </tr>
  {show_invoice_details()}
  <tr>
    <td colspan="5"><br/><br/>
      <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" colspan="3">{$radio_email}<br/><table><tr><td align="left">{$simple_or_complex_transaction}</td></tr></table></td>
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

{show_transaction_list("templates/timeSheetTransactionListR.tpl")}
{show_new_transaction("templates/timeSheetNewTransaction.tpl")}


<br><br>
<br><br>&nbsp;

{show_footer()}


