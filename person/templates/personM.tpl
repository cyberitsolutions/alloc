{page::header()}
{page::toolbar()}

<style>
  h6 { margin-top:10px; margin-bottom:3px; }
</style>

<table class="box" style="width:58% !important" align="left">
  <tr>
    <th>Person Details</th>
  </tr>
  <tr>
    <td>
      <form action="{$url_alloc_person}" method="post">
      <input type="hidden" name="personID" value="{$person_personID}">
      <table border="0" cellspacing=0 cellpadding=5>
        <tr>
          <td>First Name</td>
          <td width="30%"><input type="text" name="firstName" value="{$person_firstName}"></td>
        </tr>
        <tr>
          <td>Surname</td>
          <td><input type="text" name="surname" value="{$person_surname}"></td>
        </tr>
        <tr>
          <td>Login Username</td>
          <td><input type="text" name="username" value="{$person_username}"></td>
        </tr>
        <tr>
          <td>User Enabled</td>
          <td><input type="checkbox" name="personActive" value="1"{$personActive}></td>
        </tr>
        <tr>
          <td>Last Logged In</td>
          <td>{$person_lastLoginDate}</td>
        </tr>
        <tr>
          <td>Password</td>
          <td><input type="password" name="password1" value=""></td>
        </tr>
        <tr>
          <td>Confirm Password</td>
          <td><input type="password" name="password2" value=""></td>
        </tr>
        <tr>
          <td>Email Address</td>
          <td><input type="text" name="emailAddress" value="{$person_emailAddress}"></td>
        </tr>       
        <tr>
          <td>Phone No</td>
          <td><input type="text" name="phoneNo1" value="{$person_phoneNo1}"></td>
        </tr>
        <tr>
          <td>Mobile No</td>
          <td><input type="text" name="phoneNo2" value="{$person_phoneNo2}"></td>
        </tr>
        <tr>
          <td class="nobr" width="20%">Preferred Payment TF</td>
          <td><select name="preferred_tfID"><option value="">&nbsp;</option>{$preferred_tfID_options}</select></td>
        </tr>
        <tr>
          <td class="top">Default Rate</td>
          <td class="top">
            {if $current_user->have_perm(PERM_PERSON_WRITE_MANAGEMENT)}
            <input size="10" type="text" name="defaultTimeSheetRate" value={page::money(config::get_config_item('currency'),$person_defaultTimeSheetRate,"%mo")}>
            <select name="defaultTimeSheetRateUnitID"><option value="">{$timeSheetRateUnit_select}</select>
            {else}
              {page::money(config::get_config_item('currency'),$person_defaultTimeSheetRate,"%mo")}
              {$timeSheetRateUnit_label}
            {/}
          </td>
        </tr>
        <tr>
          <td valign="top">Special Permissions</td>
          <td>{show_perm_select()}</td>
        </tr>
        {include_employee_fields()}
        {include_management_fields()}
        <tr>
          <td align="center" colspan="4">
            {show_action_buttons()}
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
</table>

  
{if $current_user->get_id() == $person_personID}
<!-- preferences -->
<form action="{$url_alloc_settings}" method="post">
<input type="hidden" name="personID" value="{$person_personID}">
<table class="box" style="width:40% !important; margin-left:10px; border:1px solid #ccc !important;" align="left">
  <tr>
    <th>Preferences</th>
  </tr>
  <tr>
    <td>
      <div class="enclose">
        <h6>Font Size<div>Theme</div></h6>
        <div style="float:left; width:30%;">
          <select name="font">{page::select_options(page::get_customizedFont_array(), $current_user->prefs["customizedFont"])}</select>
        </div>
        <div style="float:right; width:50%;">
          <select name="theme">{page::select_options(page::get_customizedTheme_array(), $current_user->prefs["customizedTheme2"])}</select>
        </div>
      </div>

      <div class="enclose">
        <h6>Daily Email<div>Self Mail</div></h6> 
        <div style="float:left; width:30%;">
          <select name="dailyTaskEmail">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["dailyTaskEmail"])}</select>
          {page::help("<b>Daily Email</b><br><br>Control whether or not you receive a daily task email.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="receiveOwnTaskComments">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["receiveOwnTaskComments"])}</select>
          {page::help("<b>Self Mail</b><br><br>Control whether or not you receive a copy of your own comments in email discussion threads.")}
        </div>
      </div>

      <div class="enclose">
        <h6>Homepage Projects<div>Show Filters</div></h6> 
        <div style="float:left; width:30%;">
          <select name="showProjectHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showProjectHome"])}</select>
          {page::help("<b>Homepage Projects</b><br><br>Display the project list box on the home page.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showFilters">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showFilters"])}</select>
          {page::help("<b>Show Filters</b><br><br>Control whether or not the filters are displayed by default on the various tabs in alloc.")}
        </div>
      </div>

      <div class="enclose">
        <h6>Homepage Tasks<div>Homepage Calendar</div></h6> 
        <div style="float:left; width:30%;">
          <select name="showTaskListHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showTaskListHome"])}</select>
          {page::help("<b>Homepage Tasks</b><br><br>Display the task list box on the home page.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showCalendarHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showCalendarHome"])}</select>
          {page::help("<b>Homepage Calendar</b><br><br>Display the calendar box on the home page.")}
        </div>
      </div>

      <div class="enclose">
        <h6>Homepage Time Sheet Stats<div>Homepage New Time Sheet Item</div></h6> 
        <div style="float:left; width:30%;">
          <select name="showTimeSheetStatsHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showTimeSheetStatsHome"])}</select>
          {page::help("<b>Homepage Time Sheet Stats</b><br><br>Display the time sheet stats box on the home page.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showTimeSheetItemHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showTimeSheetItemHome"])}</select>
          {page::help("<b>Homepage Time Sheet Item</b><br><br>Display the add new time sheet item box on the home page.")}
        </div>
      </div>

      <div class="enclose">
        <h6>Homepage Private Mode<div>Homepage New Time Sheet Item Hint</div></h6> 
        <div style="float:left; width:30%;">
          <select name="privateMode">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["privateMode"])}</select>
          {page::help("<b>Homepage Private Mode</b><br><br>Prevent someone who is standing over your shoulder from seeing financial amounts on the homepage.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showTimeSheetItemHintHome">{page::select_options(array(0=>"No",1=>"Yes"),$current_user->prefs["showTimeSheetItemHintHome"])}</select>
          {page::help("<b>Homepage Time Sheet Item Hint</b><br><br>Display the add new time sheet item hint box on the home page.")}
          &nbsp;&nbsp;&nbsp;
          <button type="submit" name="customize_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        </div>
      </div>

    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>
<!-- end preferences -->
{/}

{if $person_personID}

<br style="clear:both">

{include_employee_skill_fields()}

<table class="box">
  <tr>
    <th class="header">Absence Forms
      <span>
        <a href="{$absence_url}">New Absence</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="sortable list">
        <tr>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Absence Type</th>
        </tr>
        {show_absence_forms("templates/personAbsenceR.tpl")}
      </table>
    </td>
  </tr>
</table>

{/}

{page::footer()}
