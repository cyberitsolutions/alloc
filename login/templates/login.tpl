{page::header()}

<div class="width">
{page::messages()}
</div>

<form action="{$url_alloc_login}" method="post" id="login_form">
{if $forward_url}
<input type="hidden" name="forwardUrl" value="{$forward_url}" />
{/}
<div class="width whitely corner shadow">
  <div id="links"><a onclick="javascript:$('.toggleable').toggle(); return false;" href="">New Password</a></div>
  
  <div class="toggleable">
    <span>Username</span>
    <span><input type="text" name="username" id="username" value="{$username}" maxlength="32"></span>
    <span>Password</span>
    <span><input type="password" id="password" name="password" maxlength="32"></span>
    <span>&nbsp;</span>
    <span style="margin:25px 5px 30px 9px"><input type="submit" name="login" value="&nbsp;&nbsp;Login&nbsp;&nbsp;"></span>
  </div>
  
  <div class="toggleable" style="display:none">
    <span>Email</span>
    <span><input type="text" name="email" size="20" maxlength="32"></span>
    <span>&nbsp;</span>
    <span style="margin:25px 5px 30px 9px"><input type="submit" name="new_pass" value="Send Password"></span>
  </div>

  <div id="footer">{$status_line}<input type="hidden" name="account" value="{$account}"></div>
</div>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{if $latest_changes}
<div class="width" style="font-size:90%">
  {$latest_changes}
</div>
{/}

{page::footer()}
