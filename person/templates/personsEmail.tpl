{:show_header}
  {:show_toolbar}
<h1>Email People</h1>
<strong>Use this function with care!</strong>
<form action="{url_alloc_emailProcessor}" method="post">
<table>
  <tr>
    <td valign="top">To:</td>
    <td><textarea name="email_to" rows="3" cols="60" wrap="virtual">{email_to}</textarea></td>
  </tr>
  <tr>
    <td valign="top">From:</td>
    <td><input type="text" name="email_from" value="{email_from}" size="60"></td>
  </tr>
  <tr>
    <td valign="top">Subject:</td>
    <td><input type="text" name="email_subject" value="{email_subject}" size="60"></td>
  </tr>
  <tr>
    <td valign="top">Message:</td>
    <td><textarea name="email_message" rows="18" cols="60" wrap="virtual">{email_message}</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" value="Send"></td>
  </tr>
</table>
</form>
{:show_footer}
