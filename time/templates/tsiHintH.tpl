<script>
  
  $(document).ready(function() {
    var prev_item_text = "";
    var timeout_id;

    function update_tsiHint_item_results() {
      var item_val = $("#tsiHint_item").val();
      if (prev_item_text != item_val) {
        prev_item_text = item_val;
        clearTimeout(timeout_id);
        timeout_id = 0;
        $.post('{$url_alloc_updateTsiHintHome}',{ "tsiHint_item" : item_val }, function(data) {
          $("#tsiHint_item_results").html(data);
          $("#tsiHint_item_results").slideDown();
        });
      }
    }

    preload_field("#tsiHint_item","USERNAME  DURATION  TASKID  COMMENT");

    $("#tsiHint_item").keyup(function(e) {
        // If space key, then refresh immediately.
        if (e.which == 32) {
          update_tsiHint_item_results();

        // Else any other key? Then refresh in a second.
        } else if (!timeout_id) {
          timeout_id = window.setTimeout(function(){
            update_tsiHint_item_results();
          },1000);
        }
    });

  });

</script>
<style>
  #tsiHint_item_results table tr td {
    border-top:none !important;
  }
</style>

<form action="{$url_alloc_home}" method="post">

<table class="list">
  <tr>
    <th>Add time item hint</th>
    <th class="right">{page::help("home_tsiHint")}</th>
  </tr>
  <tr>
    <td colspan="2">
      <input style="width:100%" type="text" name="tsiHint_item" id="tsiHint_item">
      <div id="tsiHint_item_results" class="hidden" style="margin:10px;"></div>
    </td>
  </tr>
</table>

</form>
