<script>
$(document).ready(function() {
  $.getJSON("../time/updateTimeGraph.php", function(data){
    var points = data["points"];
    var plot1 = $.jqplot('chart1', [points], {  
        series:[{ showMarker:false }],
        seriesColors: [ "#539cf6" ],
        axesDefaults: {
          tickRenderer: $.jqplot.CanvasAxisTickRenderer,
          tickOptions: {
            angle: -60,
            fontSize: '6pt'
          }
        },
        axes:{
          xaxis:{
            renderer:$.jqplot.DateAxisRenderer,
            tickOptions:{ formatString:'%b %#d' },
            tickInterval:'1 day'
          },
          yaxis:{
          min:0,
          max:12,
          tickOptions: {
            angle: 0
          }
          }
        }
    });
  });
});
</script>

<a href="{$url_alloc_timeSheetGraph}personID={echo $current_user->get_id()}&applyFilter=true"><div id="chart1" style="height:150px; margin-bottom:5px;"></div></a>

<table class='list'>
<tr>
  <th style="font-size:90%">Today:</th><td>{$hours_sum_today}hrs</td><td class="right obfuscate">{$dollars_sum_today}</td>
</tr>
<tr>
  <th style="font-size:90%">Yesterday:</th><td>{$hours_sum_yesterday}hrs</td><td class="right obfuscate">{$dollars_sum_yesterday}</td>
</tr>
<tr>
  <th style="font-size:90%">Last 2 weeks:</th><td>{$hours_sum_fortnight}hrs</td><td class="right obfuscate">{$dollars_sum_fortnight}</td>
</tr>
<tr>
  <th style="font-size:90%">2 week average:</th><td>{$hours_avg_fortnight}hrs</td><td class="right obfuscate">{$dollars_avg_fortnight}</td>
</tr>
</table>
