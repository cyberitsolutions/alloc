<script>
  
  $(document).ready(function() {
    var prev_item_text = "";
    var timeout_id;

    function update_time_item_results() {
      var item_val = $("#time_item").val();
      if (prev_item_text != item_val) {
        prev_item_text = item_val;
        clearTimeout(timeout_id);
        timeout_id = 0;
        $.post('{$url_alloc_updateTimeSheetHome}',{ "time_item" : item_val }, function(data) {
          $("#time_item_results").html(data["table"]);
          $("#time_item_results").slideDown();
        },'json');
      }
    }

    preload_field("#time_item","DURATION  TASKID  COMMENT");

    $("#time_item").keypress(function(e) {
        // If space key, then refresh immediately.
        if (e.which == 32) {
          update_time_item_results();

        } else if (e.which == 13) {
          clearTimeout(timeout_id);
          var item_val = $("#time_item").val();
          $.post('{$url_alloc_updateTimeSheetHome}',{ "save" : 1, "time_item" : item_val }, function(data) {
            $("#time_item_results").html(data["table"]);
            $("#time_item_results").slideDown();
            if (data["status"] == "good") {
              $("#time_item").val("");
            }
          },'json');
          return false;

        // Else any other key? Then refresh in a second.
        } else if (!timeout_id) {
          timeout_id = window.setTimeout(function(){
            update_time_item_results();
          },1000);
        }
    });

  });

</script>
<style>
  #time_item_results table tr td {
    border-top:none !important;
  }
</style>

<form action="{$url_alloc_home}" method="post">

<table class="list">
  <tr>
    <th>Add time</th>
    <th class="right">{page::help("home_timeSheet")}</th>
  </tr>
  <tr>
    <td colspan="2">
      <input style="width:100%" type="text" name="time_item" id="time_item">
      <div id="time_item_results" class="hidden" style="margin:10px;"></div>
    </td>
  </tr>
</table>

</form>
