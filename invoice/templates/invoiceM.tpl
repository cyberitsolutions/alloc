{page::header()}
{page::toolbar()}

<script>
$(document).ready(function() {
  $("#projectID").live('change',function() {
    makeAjaxRequest("{$url_alloc_updateTFList}projectID="+$(this).val(),"field_tfID");
  });
});
</script>

<form action="{$url_alloc_invoiceRepeat}" method="post">
<style>
  #repeating-invoice td {
    vertical-align:top;
  }
</style>
<table id="repeating-invoice" class="box hidden">
  <tr>
    <th class="header" colspan="2">Repeating Invoice
      <span>
        {page::help("
      <b>Repeating Invoices Help</b>
      <br><br>
      Use the invoice below as a template for new invoices
      that are automatically created on a particular schedule of dates. 
      This can be used for re-occurring payment models.
      <br><br>

      Additionally you can nominate to make the new invoices get automatically
      sent out to the client at the scheduled date.

      ")}
      </span>
    </th>
  </tr>
  <tr>
    <td style="padding-top:20px;width:55%">
      Create a new invoice on each of the following dates:
    </td>
  </tr>
  <tr>
    <td>
      {page::textarea("frequency",$invoiceRepeat_frequency)}
    </td>
    <td>
        <span class="calendar_container nobr">
        <img src="{$url_alloc_images}cal{echo date("Y")}.png" title="Date Selector" alt="Date Selector" id="frequency_button">
        </span>

        <script>
          $("#frequency").each(function(){
            var s = TPL_START_BRACETPL_END_BRACE
            s["inputField"] = "frequency";
            s["ifFormat"] = "%Y-%m-%d";
            s["button"] = "frequency_button";
            s["showOthers"] = 1;
            s["align"] = "Bl";
            s["firstDay"] = get_alloc_var("cal_first_day");
            s["step"] = 1;
            s["weekNumbers"] = 0;
            s["onSelect"] = function(ev) {
              $("#frequency").val($("#frequency").val()+"   "+ev.date.print(ev.dateFormat));
            }
            Calendar.setup(s);
          });
        </script>
    </td>
  </tr>
  <tr>
    <td style="padding-top:20px;">
      To (optionally) email the new invoices out on the scheduled dates, enter a message &amp; specify the recipients:
    </td>
  </tr>
  <tr>
    <td>
      {page::textarea("message",$invoiceRepeat_message,array("height"=>"medium"))}
    </td>
    <td>
        {$allParties = $invoiceRepeat->get_all_parties($invoiceID)}
        {echo interestedParty::get_interested_parties_html($allParties)}
        <input type="hidden" name="invoiceID" value="{$invoiceID}">
        <input type="hidden" name="invoiceRepeatID" value="{$invoiceRepeat_invoiceRepeatID}">
    </td>
  </tr>
  <tr>
    <td colspan="2" class="center">
        <button type="submit" name="save" value="1" class="save_button">Repeat the Invoice<i class="icon-ok-sign"></i></button>
    </td>
  </tr>
</table>
</form>


<form action="{$url_alloc_invoice}" method="post">
<table class="box">
  <tr>
    <th class="header" colspan="4">Invoice
      <span>
      {if $invoiceID}
        <a href="#x" onClick="$('#repeating-invoice').slideToggle(); return false;">Repeating Invoice</a>
        <a href="{$url_alloc_invoicePrint}invoiceID={$invoiceID}">PDF</a>
        <a href="{$url_alloc_invoicePrint}invoiceID={$invoiceID}&verbose=1">PDF+</a>
        {page::star("invoice",$invoiceID)}
      {/}
      </span>
    </th>
  </tr>  
  <tr>
    <td align="right" width="30%">Client:{page::mandatory($clientID)} </td>
    <td class="nobr" class="nobr" width="20%">{$field_clientID}</td>
    <td align="right" class="nobr" width="10%">Amount Allocated:</td>
    <td>{$field_maxAmount}</td>
  </tr>
  <tr>
    <td align="right">Project:</td>
    <td>{$field_projectID}</td>
    <td align="right" class="nobr" width="10%">Total Incoming Funds:</td>
    <td>{$invoiceTotal}</td>
  </tr>

  <tr>
    <td align="right">TF:</td>
    <td id="field_tfID">{$field_tfID}</td>
    <td align="right">Total Amount Paid:</td>
    <td>{$invoiceTotalPaid}</td>
  </tr>

  <tr>
    <td align="right">Invoice Number: </td>
    <td>{$field_invoiceNum}</td>
    <td align="right">Period: </td>
    <td>{$field_invoiceDateFrom}&nbsp;&nbsp;{$field_invoiceDateTo}</td>
  </tr>
  <tr>
    <td align="right">Invoice Name: </td>
    <td>{$field_invoiceName}</td>
  </tr>
  <tr>
    <td colspan="4" align="center">
      <input type="hidden" name="invoiceItemID" value="{$invoiceItemID}">
      <input type="hidden" name="mode" value="{$mode}">
      <table width="100%" align="center">
        <tr>
          <td align="center">
            {$invoice_buttons}<br><br>{$invoice_status_label}
            <input type="hidden" name="invoiceID" value="{$invoiceID}">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>


{if $invoiceID}

  {show_new_invoiceItem("templates/invoiceItemForm.tpl")}

  {if count(invoice::get_invoiceItems($invoiceID))}
    <table class="box">
      <tr>
        <th>Invoice Items</th>
      </tr>
      <tr>
        <td>{show_invoiceItem_list()}</td>
      </tr>
    </table>
  {/}
{/}


<input type="hidden" name="sessID" value="{$sessID}">
</form>

{if $invoiceID}
{show_comments()}
{/}
{if defined("SHOW_INVOICE_ATTACHMENTS") && SHOW_INVOICE_ATTACHMENTS}
{show_attachments($invoiceID)}
{/}

{page::footer()}
