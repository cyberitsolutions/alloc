{page::header()}
  {page::toolbar()}
  <div style="float:left; width:70%; vertical-align:top; padding:0; margin:0px; margin-right:1%; min-width:400px;">
    {show_home_items("standard",$home_items)}
  </div>
  <div style="float:left; width:29%; vertical-align:top; padding:0; margin:0px;">
    {show_home_items("narrow",$home_items)}
  </div>


  <!-- hidden preferences options. -->
  <div class="config_top_ten_tasks hidden config-pane lazy">
  </div>


  <div class="config_task_calendar_home_item hidden config-pane">
  <form action="{$url_alloc_settings}" method="post">
  <div>
    <h6>Calendar Weeks<div>Weeks Back</div></h6> 
    <div style="float:left; width:30%;">
      <select name="weeks">{page::select_options(array("0"=>0,1=>1,2=>2,3=>3,4=>4,8=>8,12=>12,30=>30,52=>52), $current_user->prefs["tasksGraphPlotHome"])}</select>
      {page::help("<b>Calendar Weeks</b><br><br>Control the number of weeks that the home page calendar displays.")}
    </div>
    <div style="float:right; width:50%;">
      <select name="weeksBack">{page::select_options(array("0"=>0,1=>1,2=>2,3=>3,4=>4,8=>8,12=>12,30=>30,52=>52), $current_user->prefs["tasksGraphPlotHomeStart"])}</select>
      {page::help("<b>Weeks Back</b><br><br>Control how many weeks in arrears are displayed on the home page calendar.")}
    </div>
  </div>
  <br><br>
  <span style="float:right">
    <a href="#x" onClick="$(this).parent().parent().parent().fadeOut();">Cancel</a>
    <button type="submit" name="customize_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
  </span>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>
  </div>


  <div class="config_project_list hidden config-pane">
  <form action="{$url_alloc_settings}" method="post">
  <div>
    <h6>Project List</h6> 
    <div style="float:left; width:30%;">
      <select name="projectListNum">{page::select_options(array("0"=>0,5=>5,10=>10,15=>15,20=>20,30=>30,40=>40,50=>50,"all"=>"All"), $current_user->prefs["projectListNum"])}</select>
      {page::help("<b>Project List</b><br><br>Control the number of projects displayed on your home page.")}
    </div>
  </div>
  <br><br>
  <span style="float:right">
    <a href="#x" onClick="$(this).parent().parent().parent().fadeOut();">Cancel</a>
    <button type="submit" name="customize_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
  </span>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>
  </div>

  <div class="config_time_list hidden config-pane">
  <form action="{$url_alloc_settings}" method="post">
    <div>
      <h6>Time Sheet Hours<div>Time Sheet Days</div></h6> 
      <div style="float:left; width:30%;">
        <input type="text" size="5" name="timeSheetHoursWarn" value="{echo $current_user->prefs["timeSheetHoursWarn"]}">
        {page::help("<b>Time Sheet Hours</b><br><br>Time sheets that go over this number of hours and are still in edit status will be flagged for you.")}
      </div>
      <div style="float:right; width:50%;">
        <input type="text" size="5" name="timeSheetDaysWarn" value="{echo $current_user->prefs["timeSheetDaysWarn"]}">
        {page::help("<b>Time Sheet Days</b><br><br>Time sheets that are older than this many days and are still in edit status will be flagged for you.")}
      </div>
    </div>
  <br><br>
  <span style="float:right">
    <a href="#x" onClick="$(this).parent().parent().parent().fadeOut();">Cancel</a>
    <button type="submit" name="customize_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
  </span>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>
  </div>

{page::footer()}
