<style>
  h6 { margin-top:10px; margin-bottom:3px; }
</style>
<form action="{$url_alloc_home}" method="post">
<table cellspacing="0" style="width:100%">
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
        <div style="float:right; width:50%;">
          <select multiple name="topTasksStatus[]">{$topTasksStatusOptions}</select>
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
        <h6>Daily Email<div>Receive My Comments</div></h6> 
        <div style="float:left; width:30%;">
          <select name="dailyTaskEmail">{$dailyTaskEmailOptions}</select>
          {page::help("<b>Daily Email</b><br><br>Control whether or not you receive a daily task email.")}
        </div>
        <div style="float:right; width:50%;">
          <select name="receiveOwnTaskComments">{$receiveOwnTaskCommentsOptions}</select>
          {page::help("<b>Receive My Comments</b><br><br>Control whether or not you receive a copy of your own comments in email discussion threads.")}
        </div>
      </div>
      <div class="view">
        <h6>Daily Email<div>Receive My Comments</div></h6> 
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
        <h6>Private Mode<div></div></h6> 
        <div style="float:left; width:30%;">
          <input type="checkbox" name="privateMode" value="1" {$privateMode and print "checked"}>
          {page::help("<b>Private Mode</b><br><br>Prevent someone who is standing over your shoulder from seeing financial amounts on the homepage.")}
        </div>
        <div style="float:right; width:50%;" class="right">
          <input type="button" value="Cancel" onClick="toggle_view_edit();">
          <input type="submit" name="customize_save" value="Save">
        </div>
      </div>
      <div class="view">
        <h6>Private Mode<div></div></h6> 
        <div style="float:left; width:30%;">
          {print $privateMode ? "Yes" : "No"}&nbsp;
        </div>
        <div style="float:right; width:50%;" class="right">
          <input type="button" value="Edit" onClick="toggle_view_edit();">
        </div>
      </div>

    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>
