{$extra_footer_stuff}&nbsp;
      </div> <!-- end #main2 -->
    </div> <!-- end #main -->
    {if $slowest_query_time >1}
    <div id="slowest_query_1" style="text-align:right; font-size:70%;" class="faint">
      {page::expand_link("slowest_query_2", $slowest_query_time, "slowest_query_1")}
    </div>
    <div id="slowest_query_2" style="text-align:right; font-size:70%; display:none" class="faint">
      Slowest page query {page::expand_link("slowest_query_2", $slowest_query_time, "slowest_query_1")}
      <pre>{$slowest_query}</pre>
    </div> 
    {/}
  </body>
</html>
