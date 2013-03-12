{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
  {if $timeSheet_timeSheetID}
  $(document).ready(function() {
    var orig_projectID = $("#projectID").val();
    $("#projectID").on("change",function(){
      if ($(this).val() != orig_projectID) {
        window.alert("WARNING: Changing the project will update this time sheet with new rates.\n\nAlso, this change will not work unless all the tasks that are being billed for, belong to the project:\n\n"+$(this).val()+" "+$('#projectID option:selected').text());
      }
    });
  });
  {/}
</script>
<form action="{$url_alloc_timeSheet}" method="post" id="timeSheetForm">
<input type="hidden" name="timeSheetID" value="{$timeSheet_timeSheetID}">
<input type="hidden" name="timeSheet_personID" value="{$timeSheet_personID}">
<input type="hidden" name="projectID" value="{$timeSheet_projectID}">
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

<table class="box">
  <tr>
    <th colspan="1">{page::help("timesheet_overview")}</th>
    <th class="header" colspan="4">Time Sheet
      <span>
      {if $timeSheet_timeSheetID}
        {$timeSheetPrintOptions = config::get_config_item("timeSheetPrintOptions")}
        {$timeSheetPrint = config::get_config_item("timeSheetPrint")}
        {foreach $timeSheetPrint as $value}
          <a href="{$url_alloc_timeSheetPrint}timeSheetID={$timeSheet_timeSheetID}&{$value}">{$timeSheetPrintOptions.$value}</a>
        {/}
        {page::star("timeSheet",$timeSheet_timeSheetID)}
      {/}
      </span>
    </th>
  </tr>

  <tr>
    <td>&nbsp;</td>
    <td width="20%" align="right">Created By:</td>
    <td>{=$timeSheet_personName}</td>
    <td align="right">Client:</td>
	  <td>{$show_client_options}</td>
  </tr>

  <tr>
	  <td>{page::help("engineer_amount")}</td>
    <td align="right" style="vertical-align:top;">Amount:</td>
    <td>
      <span style="float:left">{$total_dollars}&nbsp;</span>
      {if $timeSheet_status && $timeSheet_status != "finished" && $timeSheet_status != "edit" && $ts_rate_editable && ($is_manager || $is_admin)}
        <button type="submit" name="updateRate" value="1" class="filter_button"
        style="float:left;font-size:80%;padding:1px;"><i class="icon-repeat" style="margin:0px;"></i></button>
      {/}

    </td>
    <td align="right">Project:{page::mandatory($timeSheet_projectID)}</td>
	  <td width="30%" class="nobr"><div id="projectDropdown" style="display:inline">{$show_project_options}</div></td>
  </tr>

  <tr>
	  <td>{page::help("client_billing")}</td>
    <td align="right" style="vertical-align:top;">Client Billing:</td>
    <td>
      <span style="float:left" class="nobr">{$total_customerBilledDollars}{$ex_gst}&nbsp;</span>
      {if $timeSheet_status && $timeSheet_status != "finished" && $timeSheet_status != "edit" && $ts_rate_editable && ($is_manager || $is_admin)}
        <button type="submit" name="updateCB" value="1" class="filter_button"
        style="float:left; font-size:80%;padding:1px;"><i class="icon-repeat" style="margin:0px;"></i></button>
      {/}
    </td>
    <td align="right">Time Sheet Manager{$manager_plural}:</td>
    <td>{=$managers}</td>
  </tr>

  <tr>
	  <td>&nbsp;</td>
    <td align="right">Units:</td>
    <td>{$total_units}</td>
    <td align="right"><nobr>Date Submitted to Manager{$manager_plural}:</nobr></td>
    <td>{$timeSheet_dateSubmittedToManager}</td>
  </tr>

	<tr>
	  <td>{page::help("which_tf_to_credit")}</td>
 	  <td align="right">Tagged Fund:</td>
 	  <td align="left" class='{$recipient_tfID_class}'>{=$recipient_tfID_name}</td>
    <td align="right">Approved by Manager:</td>
    <td>{=$timeSheet_approvedByManagerPersonID_username}</td>
  </tr>

	<tr>
	  <td>&nbsp;</td>
    <td align="right">Period:</td>
    <td><nobr>{$period}</nobr></td>
    <td align="right">Date Submitted to Administrator:</td>
    <td>{$timeSheet_dateSubmittedToAdmin}</td>
	</tr>

	<tr>
    <td>{page::help("timesheet_add_invoice")}</td>
    <td align="right" valign="top">Attached to Invoice:</td>
    <td class="nobr">{$attach_to_invoice_button}{$invoice_link} {$amount_allocated_label} <b>{$amount_used}{$amount_allocated}</b></td>
    <td align="right">Approved by Administrator:</td> 
    <td>{$timeSheet_approvedByAdminPersonID_username}</td>
  </tr>

  {if $timeSheet_billingNote}
  <tr>
    <td valign="top"></td>
    <td align="right" valign="top">Billing Note</td>
    <td colspan="2" valign="top">{if $timeSheet_status != "finished"}
                                   {page::textarea("timeSheet_billingNote",$timeSheet_billingNote)}
                                 {else}
                                   {$timeSheet_billingNote}
                                 {/}
    </td>
  </tr>
  {/}

  <tr>
    <td colspan="5" style="padding:0;margin:0;">
      <table width="100%" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center" colspan="3">{$radio_email}</td>
        </tr>
        <tr>
          <td width="1%">{page::help("timesheet_buttons")}</td>
          <td align="center">{$timeSheet_ChangeStatusButton}<br><br>{if $timeSheet_timeSheetID}{$timeSheet_status_text}{/}</td>
          <td>&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


{show_new_timeSheet("templates/timeSheetItemForm.tpl")}

{show_transaction_list("templates/timeSheetTransactionListM.tpl")}

{show_main_list()}

{show_comments()}


<br><br>
<br><br>&nbsp;

{page::footer()}


