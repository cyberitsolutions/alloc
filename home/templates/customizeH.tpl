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
          {$fontLabel}
        </div>
        <div style="float:right; width:50%;">
          {$themeLabel}
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
          {$topTasksNumLabel}
        </div>
        <div style="float:right; width:50%;">
          {$topTasksStatusLabel}
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
          {$weeksLabel}
        </div>
        <div style="float:right; width:50%;">
          {$weeksBackLabel}
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
          {$dailyTaskEmailLabel}
        </div>
        <div style="float:right; width:50%;">
          {$receiveOwnTaskCommentsLabel}
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
          {$projectListNumLabel}
        </div>
        <div style="float:right; width:50%;">
	{$showFiltersLabel}
        </div>
      </div>

      <div class="edit">
        <h6><div></div></h6> 
        <div style="float:left; width:30%;">
        </div>
        <div style="float:right; width:50%;" class="right">
          <input type="button" value="Cancel" onClick="$('.edit').hide();$('.view').show();">
          <input type="submit" name="customize_save" value="Save">
        </div>
      </div>
      <div class="view">
        <h6><div></div></h6> 
        <div style="float:left; width:30%;">
        </div>
        <div style="float:right; width:50%;" class="right">
          <input type="button" value="Edit" onClick="$('.view').hide();$('.edit').show();">
        </div>
      </div>

    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>
