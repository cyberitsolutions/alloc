      <!-- we need to have display:table because the default is for .filter's to be hidden -->
      <table align="center" class="filter corner" style="display:table">
        <tr>
          <td colspan="6" class="center" style="padding-top:0px; padding-bottom:10px;">
            {page::side_by_side_links(array("simple_search"=>"Simple"
                                           ,"advanced_search"=>"Advanced")
                                     ,$url_alloc_transactionList."tfID=".$tfID)}
          </td>
        </tr>
        <tr>
          <td>
            <div id="advanced_search">
            <form action="{$url_alloc_transactionList}" method="get">
            <table>
              <tr>
                <td align="left">Start Date</td>
                <td align="left">End Date</td>
                <td align="left">Type</td>
                <td align="left">Status</td>
                <td align="left">Sort By</td>
                <td align="left">&nbsp;</td>
              </tr>
              <tr>
                <td>{page::calendar("startDate",$startDate)}</td>
                <td>{page::calendar("endDate",$endDate)}</td>
                <td><select name="transactionType"><option value=""> {$transactionTypeOptions}</select></td>
                <td><select name="status"><option value=""> {$statusOptions}</select></td>
                <td>
                  <input type="radio" id="st_sd" name="sortTransactions" value="transactionSortDate"{$checked_transactionSortDate}> 
                  <label for="st_sd">Last Modified</label><br>
                  <input type="radio" id="st_td" name="sortTransactions" value="transactionDate"{$checked_transactionDate}> 
                  <label for="st_td">Transaction Date</label>
                </td>
                <td><input type="hidden" name="tfID" value="{$tfID}">
                    <button type="submit" name="download" value="1" class="filter_button">CSV<i class="icon-download"></i></button>
                    <button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
                    <input type="hidden" name="sbs_link" value="advanced_search">
                </td>
              </tr>
            </table>
			      <input type="hidden" name="sessID" value="{$sessID}">
			      </form>
            </div>

          </td>
        </tr>
        <tr>
          <td align="center" colspan="10">
            <div id="simple_search">{$month_links}</div> 
          </td>
        </tr>
      </table>


