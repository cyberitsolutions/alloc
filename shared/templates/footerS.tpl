      </div> <!-- end #main2 -->
    </div> <!-- end #main -->
    {if $slowest_query_time >1}
    <div id="slowest_query_1" style="text-align:right; font-size:70%;" class="faint noprint">
      {page::expand_link("slowest_query_2", $slowest_query_time, "slowest_query_1")}
    </div>
    <div id="slowest_query_2" style="text-align:right; font-size:70%; display:none" class="faint noprint">
      Slowest page query {page::expand_link("slowest_query_2", $slowest_query_time, "slowest_query_1")}
      <pre class="message left noprint">{$slowest_query}</pre>
    </div> 
    {/}
    <div id="all_page_queries_1" style="text-align:right; font-size:70%;" class="faint noprint">
      {foreach $all_page_queries as $info}
        {$sum += $info["time"]}
      {/}
      {page::expand_link("all_page_queries_2", "Page queries: ".count($all_page_queries)." Time: ".$sum, "all_page_queries_1")}
    </div>
    <div id="all_page_queries_2" style="text-align:right; font-size:70%; display:none" class="faint noprint">
      All page queries: {page::expand_link("all_page_queries_2", "Page queries: ".count($all_page_queries)." Time: ".$sum, "all_page_queries_1")}
      <pre class='message left noprint'>
        {foreach $all_page_queries as $info}
<b>{$info.time}</b>   {=$info.query}<br>
        {/}
      </pre>
    </div> 

  </body>
</html>
