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
        </div>
        <div style="float:right; width:50%;">
          <select multiple name="topTasksStatus[]">{$topTasksStatusOptions}</select>
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
        </div>
        <div style="float:right; width:50%;">
          <select name="weeksBack">{$weeksBackOptions}</select>
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
        </div>
        <div style="float:right; width:50%;">
          <select name="receiveOwnTaskComments">{$receiveOwnTaskCommentsOptions}</select>
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
        </div>
        <div style="float:right; width:50%;">
	        <select name="showFilters">{$showFiltersOptions}</select>
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
        </div>
        <div style="float:right; width:50%;">
	        <input type="text" size="5" name="timeSheetDaysWarn" value="{$timeSheetDaysWarn}">
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
