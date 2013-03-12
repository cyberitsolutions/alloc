{page::header()}
{page::toolbar()}
<script>
$(document).ready(function() {
  //var points = { $points };

  var points = {echo alloc_json_encode($chart1)};
  if (!points) {
    points = '';
  }

  var plot1 = $.jqplot('chart1', [points], {  
    series:[{ showMarker:false }],
    seriesColors: [ "#539cf6" ],
    seriesDefaults:{
        pointLabels: { show: true }
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
        renderer:$.jqplot.DateAxisRenderer,
        tickOptions:{ formatString:'%b %#d' },
        tickInterval:'1 {$groupBy}',
        label: "Date"
      },
      yaxis:{
        min:0,
        {$max = 24; $groupBy == "month" and $max = 10*28}
        max:{$max},
        tickOptions: { angle: 0 },
        label: "Hours"
      }
    }
  });


//  var s1 = [2, 6, 7, 10];
//  var s2 = [7, 5, 3, 2];
//  var s3 = [14, 9, 3, 8];
//  var s4 = [14, 9, 3, 8];
//  var ticks = ['alex', 'beep', 'celcius', 'diagraph'];
//
//  plot2 = $.jqplot('chart2', [s1,s2,s3,s4], {
//    stackSeries: true,
//    seriesDefaults:{
//      renderer:$.jqplot.BarRenderer,
//      pointLabels: { show: true }
//    },
//    seriesColors:['#00749F', '#73C774', '#C7754C', '#17BDB8'],
//    axesDefaults: {
//      tickRenderer: $.jqplot.CanvasAxisTickRenderer,
//      tickOptions: {
//        angle: -60,
//        fontSize: '6pt'
//      },
//      legend: {
//        show: true,
//        location: 'e',
//        labels:['Fog', 'Rain', 'Frost', 'Sleet', 'Hail', 'Snow'],
//        placement: 'outside'
//      },
//    },
//    axes:{
//      xaxis:{
//        renderer: $.jqplot.CategoryAxisRenderer,
//        label: "Date",
//        ticks: ticks
//      },
//      yaxis:{
//        min:0,
//        max:24,
//        tickOptions: { angle: 0 },
//        label: "Hours"
//      }
//    },
// series: [
//        { label: 'Beans' },
//        { label: 'Oranges' },
//        { label: 'Ores' },
//        { label: 'Crackers' }
//       ],
//    legend: {
//      show: true,
//      location: 'e',
//      placement: 'outside'
//    }     
//
//
//  });


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
