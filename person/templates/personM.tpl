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
<form action="{$url_alloc_home}" method="post">
<table class="box" style="width:40% !important; margin-left:10px; border:1px solid #ccc !important;" align="left">
  <tr>
    <th>Preferences</th>
  </tr>
  <tr>
    <td>

      <div class="edit">
        <h6>Font Size<div>Theme</div></h6>
        <div style="float:left; width:30%;">
          <select name="font">{$fontOptions}</select>
        </div>
        <div style="float:right; width:50%;">
          <select name="theme">{$themeOptions}</select>
        </div>
      </div>
      <div class="view">
        <h6>Font Size<div>Theme</div></h6>
        <div style="float:left; width:30%;">
          {$fontLabel}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$themeLabel}&nbsp;
        </div>
      </div>

      <div class="edit">
        <h6>Top Tasks<div>Status</div></h6> 
        <div style="float:left; width:30%;">
          <select name="topTasksNum">{$topTasksNumOptions}</select>
          {page::help("<b>Top Tasks</b><br><br>Control the number of tasks displayed on the home page.")}
        </div>
        <div style="float:right; width:50%;" class="nobr">
          <select multiple="true" name="topTasksStatus[]">{$topTasksStatusOptions}</select>
          {page::help("<b>Status</b><br><br>Control the status of the tasks that are displayed on the home page.")}
        </div>
      </div>
      <div class="view">
        <h6>Top Tasks<div>Status</div></h6> 
        <div style="float:left; width:30%;">
          {$topTasksNumLabel}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$topTasksStatusLabel}&nbsp;
        </div>
      </div>

      <div class="edit">
        <h6>Calendar Weeks<div>Weeks Back</div></h6> 
        <div style="float:left; width:30%;">
          <select name="weeks">{$weeksOptions}</select>
          {page::help("<b>Calendar Weeks</b><br><br>Control the number of weeks that the home page calendar displays.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="weeksBack">{$weeksBackOptions}</select>
          {page::help("<b>Weeks Back</b><br><br>Control how many weeks in arrears are displayed on the home page calendar.")}
        </div>
      </div>
      <div class="view">
        <h6>Calendar Weeks<div>Weeks Back</div></h6> 
        <div style="float:left; width:30%;">
          {$weeksLabel}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$weeksBackLabel}&nbsp;
        </div>
      </div>

      <div class="edit">
        <h6>Daily Email<div>Self Mail</div></h6> 
        <div style="float:left; width:30%;">
          <select name="dailyTaskEmail">{$dailyTaskEmailOptions}</select>
          {page::help("<b>Daily Email</b><br><br>Control whether or not you receive a daily task email.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="receiveOwnTaskComments">{$receiveOwnTaskCommentsOptions}</select>
          {page::help("<b>Self Mail</b><br><br>Control whether or not you receive a copy of your own comments in email discussion threads.")}
        </div>
      </div>
      <div class="view">
        <h6>Daily Email<div>Self Mail</div></h6> 
        <div style="float:left; width:30%;">
          {$dailyTaskEmailLabel}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$receiveOwnTaskCommentsLabel}&nbsp;
        </div>
      </div>

      <div class="edit">
        <h6>Project List<div>Show Filters</div></h6> 
        <div style="float:left; width:30%;">
          <select name="projectListNum">{$projectListNumOptions}</select>
          {page::help("<b>Project List</b><br><br>Control the number of projects displayed on your home page.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showFilters">{$showFiltersOptions}</select>
          {page::help("<b>Show Filters</b><br><br>Control whether or not the filters are displayed by default on the various tabs in alloc.")}
        </div>
      </div>
      <div class="view">
        <h6>Project List<div>Show Filters</div></h6> 
        <div style="float:left; width:30%;">
          {$projectListNumLabel}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$showFiltersLabel}&nbsp;
        </div>
      </div>

      <div class="edit">
        <h6>Time Sheet Hours<div>Time Sheet Days</div></h6> 
        <div style="float:left; width:30%;">
          <input type="text" size="5" name="timeSheetHoursWarn" value="{$timeSheetHoursWarn}">
          {page::help("<b>Time Sheet Hours</b><br><br>Time sheets that go over this number of hours and are still in edit status will be flagged for you.")}
        </div>
        <div style="float:right; width:50%;">
          <input type="text" size="5" name="timeSheetDaysWarn" value="{$timeSheetDaysWarn}">
          {page::help("<b>Time Sheet Days</b><br><br>Time sheets that are older than this many days and are still in edit status will be flagged for you.")}
        </div>
      </div>
      <div class="view">
        <h6>Time Sheet Hours<div>Time Sheet Days</div></h6> 
        <div style="float:left; width:30%;">
          {$timeSheetHoursWarn}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {$timeSheetDaysWarn}&nbsp;
        </div>
      </div>


      <div class="edit">
        <h6>Time Sheet Stats<div>New Time Sheet Item</div></h6> 
        <div style="float:left; width:30%;">
          <select name="showTimeSheetStats">{page::select_options(array(0=>"No",1=>"Yes"),$showTimeSheetStats)}</select>
          {page::help("<b>Show Time Sheet Stats</b><br><br>Show the time sheet stats box on the homepage.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showNewTimeSheetItem">{page::select_options(array(0=>"No",1=>"Yes"),$showNewTimeSheetItem)}</select>
          {page::help("<b>Show New Time Sheet Item</b><br><br>Show the new time sheet item input on the homepage.")}
        </div>
      </div>
      <div class="view">
        <h6>Time Sheet Stats<div>New Time Sheet Item</div></h6> 
        <div style="float:left; width:30%;">
          {print $showTimeSheetStats ? "Yes" : "No"}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {print $showNewTimeSheetItem ? "Yes" : "No"}&nbsp;
        </div>
      </div>



      <div class="edit">
        <h6>Private Mode<div>New Time Sheet Item Hint</div></h6> 
        <div style="float:left; width:30%;">
          <input type="hidden" name="form_on_person_page" value="1">
          <select name="privateMode">{page::select_options(array(0=>"No",1=>"Yes"),$privateMode)}</select>
          {page::help("<b>Private Mode</b><br><br>Prevent someone who is standing over your shoulder from seeing financial amounts on the homepage.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="showNewTsiHintItem">{page::select_options(array(0=>"No",1=>"Yes"),$showNewTsiHintItem)}</select>
          {page::help("<b>Show New Time Sheet Item Hint</b><br><br>Show the new time sheet item hint input on the homepage.")}
          &nbsp;&nbsp;&nbsp;
          <a href="" onClick="return toggle_view_edit(true);">Cancel edit</a>&nbsp;&nbsp;
          <button type="submit" name="customize_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        </div>
      </div>
      <div class="view">
        <h6>Private Mode<div>New Time Sheet Item Hint</div></h6> 
        <div style="float:left; width:30%;">
          {print $privateMode ? "Yes" : "No"}&nbsp;
        </div>
        <div style="float:right; width:50%;">
          {print $showNewTsiHintItem ? "Yes" : "No"}&nbsp;&nbsp;&nbsp;
          <button type="button" onClick="toggle_view_edit();">Edit<i class="icon-edit"></i></button>
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
          <th>ID</th>
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
