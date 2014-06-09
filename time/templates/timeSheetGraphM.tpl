{page::header()}
{page::toolbar()}
<script>
$(document).ready(function() {
  var points = {echo alloc_json_encode($chart1)};
  if (!points) {
    points = '';
  }

  var plot1 = $.jqplot('chart1', [points], {  
    series:[{ showMarker:false }],
    seriesColors: [ "#539cf6" ],
    seriesDefaults:{
        pointLabels: { show: true },
        renderer: $.jqplot.BarRenderer,
        rendererOptions: {
            barPadding: 10,
            barMargin: 10,
            barWidth: 40
        }
    },
    axesDefaults: {
      tickRenderer: $.jqplot.CanvasAxisTickRenderer,
      tickOptions: {
        angle: -60,
        fontSize: '6pt'
      }
    },
    axes:{
      xaxis:{
        min:"{$dateFrom} 12:00PM",
        max:"{$dateTo} 12:00PM",
        renderer:$.jqplot.DateAxisRenderer,
        tickOptions:{ formatString:'%b %#d' },
        tickInterval:'1 {$groupBy}',
        label: "Date",
      },
      yaxis:{
        min:0,
        {$max = 24; $groupBy == "month" and $max = 10*28}
        max:{$max},
        tickOptions: { angle: 0 },
        label: "Hours",
      }
    }
  });


});
</script>


<table class="box">
  <tr>
    <th class="header">Time Sheet Graphs
      <!-- <b> - {print count($timeSheetListRows)} records</b> -->
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      {show_filter()}
    </td>
  </tr>
  <tr>
    <td>
      <div id="chart1" style="margin:0 auto; min-width:800px; margin-bottom:5px;"></div>
      <div id="chart2" style="margin:0 auto; min-width:800px; margin-bottom:5px;"></div>
    </td>
  </tr>
</table>
{page::footer()}
