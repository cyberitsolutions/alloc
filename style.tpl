body { 
  (BODY_BACKGROUND); 
  font-family:arial,helvetica,sans-serif; 
  color:(BODY_COLOR); 
  margin:0px 14px;
  font-size:100% !important; font-size:(DEFAULT_FONT_SIZE)px;
}

/* Need this for IE 5, so that the font sizes within tables are not ridiculously huge */
body table {
  font-size:100% !important; font-size:(DEFAULT_FONT_SIZE)px;
}

a:link, a:visited { 
  color:(A_COLOR);
}
a:hover { 
  color:(A_HOVER); 
}
h1 { 
  font-weight:bold; 
  font-size:(H1_FONT_SIZE)px; 
}
h2 { 
  font-weight:bold; 
  font-size:(H2_FONT_SIZE)px; 
}
h3 { 
  font-weight:bold; 
  font-size:(H3_FONT_SIZE)px; 
}
textarea, input, select, textarea { 
  font-size:(DEFAULT_FONT_SIZE)px; 
}
th { 
  color:(TD_COLOR); 
  text-align:left; 
}
td { 
  color:(TD_COLOR); 
  font-size:(DEFAULT_FONT_SIZE)px; 
}
ul { 
  display:inline; 
  list-style:circle inside; 
}
form { 
  display:inline; 
  padding:0px; 
  margin:0px;
}

.right { 
  text-align:right; 
} 
.left { 
  text-align:left; 
} 
.center { 
  text-align:center; 
} 
.nobr { 
  white-space:nowrap 
}




#helper { 
  position:absolute; 
  visibility:hidden; 
  z-index:200;  
}
table.helper_table { 
  background-color:#fffdf2; 
  color:#666666; 
  border:1px solid #999999; 
  width:400px; 
  font-size:10px;
}



div.message { 
  padding:4px 4px; 
  margin:14px auto 7px auto; 
  border:1px solid #cccccc;
  background-color:#fffdf2; 
  width:60%; 
}
.bad { 
  color:(TRANSACTION_REJECTED); 
  font-size:(TABLE_BOX_TH_FONT_SIZE)px; 
}
.good { 
  color:(TRANSACTION_APPROVED); 
  font-size:(TABLE_BOX_TH_FONT_SIZE)px; 
}
.help { 
  color:(TRANSACTION_PENDING);  
  font-size:(TABLE_BOX_TH_FONT_SIZE)px;
}


/* Main content boxes */ 
table.box { 
  border:(TABLE_BOX_BORDER_PX) solid (TABLE_BOX_BORDER); 
  background-color:(TABLE_BOX_BACKGROUND_COLOR); 
  margin:8px; 
  margin-top:14px; 
  margin-bottom:0px; 
  vertical-align:top; 
  width:98%; 
} 
table.box th { 
  (TABLE_BOX_TH_BACKGROUND); 
  color:(TABLE_BOX_TH_COLOR); 
  font-size:(TABLE_BOX_TH_FONT_SIZE)px; 
} 
table.box th.center { 
  text-align:center; 
} 
table.box th.right table { 
  width:40%; 
} 

table.box th a:link, table.box th a:visited { 
  font-weight:bold; 
  color:(TABLE_BOX_TH_A_LINK_COLOR); 
  font-size:(TABLE_BOX_TH_A_FONT_SIZE)px; 
}
table.box th a:hover { 
  font-weight:bold; 
  color:(TABLE_BOX_TH_A_HOVER_COLOR); 
  font-size:(TABLE_BOX_TH_A_FONT_SIZE)px; 
}

/* Calendar */
table.calendar { 
  width:100%; 
  border-right:1px solid (PANE_FRAME_COLOR); 
  border-bottom:1px solid (PANE_FRAME_COLOR);
} 
table.calendar td { 
  background-color:(TABLE_BOX_BACKGROUND_COLOR); 
  border-left:1px solid (PANE_FRAME_COLOR);  
  border-top:1px solid (PANE_FRAME_COLOR); 
  width:10%; vertical-align:top;
  font-size:smaller;
}
table.calendar td.today { 
  background-color:#fffdf2; 
  border-top:2px solid (PANE_FRAME_COLOR);  
  border-left:2px solid (PANE_FRAME_COLOR);  
  border-right:1px solid (PANE_FRAME_COLOR);  
  border-bottom:1px solid (PANE_FRAME_COLOR); 
}
table.calendar td.today h1 {
  font-weight:bold;
}
table.calendar td.even { 
  background-color:(PANE_BG_COLOR); 
}
table.calendar td.absent {
  background-color:#cccccc;
  color:#999999;
}
table.calendar td.absent h1{
  color:#999999;
}
table.calendar h1 {
  text-align:right;
  font-size:(DEFAULT_FONT_SIZE)px;
  margin:0px;
  padding:3px;
  font-weight:normal;
}
table.calendar img {
  float:left;
  margin:0px;
}


/* Filters */
table.filter { 
  background-color:(PANE_BG_COLOR); 
  border:2px dashed (PANE_FRAME_COLOR); 
  padding:4px; 
  margin:4px; 
  margin-top:14px; 
  text-align:left;
}
table.filter th { 
  font-weight:bold; 
  background-color:(PANE_BG_COLOR); 
  color:(TD_COLOR); 
  border-bottom:0px solid #ffffff; 
  text-align:center; 
}

table.panel { 
  background-color:(PANE_BG_COLOR); 
  height:100%; 
  border:1px solid (PANE_FRAME_COLOR); 
}

tr.odd { 
  background-color:(PANE_BG_COLOR); 
}
tr.even { 
  background-color:(TR_EVEN_BACKGROUND_COLOR); 
}

.overdue { 
  color: (TRANSACTION_REJECTED); 
}
.really_overdue { 
  font-weight:bold; 
  color: (TRANSACTION_REJECTED);
}
.behind-target { 
  color: (BEHIND_TARGET); 
}
.transaction-approved { 
  color: (TRANSACTION_APPROVED); 
}
.transaction-pending { 
  color: (TRANSACTION_PENDING); 
}
.transaction-rejected { 
  color: (TRANSACTION_REJECTED); 
}
.highlighted { 
  font-weight:normal; 
  font-style:normal; 
  background-color:(HIGHLIGHTED_BACKGROUND_COLOR); 
  padding:0px 3px;
}

table.tasks { 
  width:100%; 
  border-right:1px solid (PANE_FRAME_COLOR); 
  border-bottom:1px solid (PANE_FRAME_COLOR);
}
td.tasks { 
  background-color: (PANE_BG_COLOR); 
  padding:4px; 
}
table.tasks th, table.calendar th { 
  border-bottom:0px; 
  font-weight:bold; 
  white-space:nowrap; 
  background:(PANE_BG_COLOR_HEADER); 
  color:(TD_COLOR); 
}
.col { 
  border-left:1px solid (PANE_FRAME_COLOR); 
  border-top:1px solid (PANE_FRAME_COLOR); 
  padding:4px;
}

.grand_total { 
  border-top:1px solid black;
  text-align:right;
}

table.comments { 
  border:1px solid (PANE_FRAME_COLOR); 
  background:(PANE_BG_COLOR); 
  margin-bottom:4px; 
  margin-top:2px;
}
table.comments th { 
  text-align:left; 
  background:(PANE_BG_COLOR); 
  vertical-align:top; 
  color:(TD_COLOR); 
  font-weight:normal; 
  padding:4px; 
}
table.comments td { 
  padding:4px; 
}


/* Setup the main content box that encapsulates everything */
div#main {
  width:100%;
  padding-bottom:20px;
  background-color: white;
  margin:auto;
  border:1px solid #9c9c9c;
  border-left:1px solid #9c9c9c;
  border-right:1px solid #9c9c9c;
  position: relative;
  clear:both;
  text-align:left;
  margin-bottom:4px;
}

div#tabs {
  width:100%;
  margin:auto;
  border:0px;
  position: relative;
  clear:both;
  text-align:left;
  height:27px;
  top:1px;
  left:1px !important; left:0px;
  z-index:1;
}

div#header {
  display:inline;
  margin-left:0px;
  margin-top:6px;
  position:relative;
  text-align:left;
  float:left;
  font-size:120%;
  color:#444444;
}

div#header p {
  padding:0px;
  margin:0px;
  display:inline;
  position:relative;
  top:-6px;
}


/* A single image based tab */
div.tab {
  float:left;
  position:absolute;
  background:url(../images/tab_unselected.gif) top no-repeat;
  display:inline;
  width:81px;
  height:27px;
  text-align:center;
  font-size:(DEFAULT_FONT_SIZE)px;
}

/* Active tabs use this class as well */
div.active {
  background:url(../images/tab_selected.gif) top no-repeat;
}

div.tab a {
  font-weight:bold;
  text-decoration:none;
  position:relative;
  top:8px;
  padding: 5px 10px;
}

div.tab a:link          { color:(TABLE_MENU_A_COLOR);  }
div.tab a:visited       { color:(TABLE_MENU_A_COLOR);  }
div.tab a:hover         { color:(TABLE_MENU_A_HOVER_COLOR); }

div.active a:link, div.active a:visited {
  color:(TABLE_MENU_A_ACTIVE_COLOR);
}


div#menu_top_right {
  float:right;
  text-align:right;
  position:relative;
  display:inline;
  font-size:(DEFAULT_FONT_SIZE)px;
  font-weight:bold;
  color:(TABLE_MENU_A_COLOR);
  padding:0px;
  margin:0px;
  margin-top:10px;
}

div#menu_top_right table, div#menu_top_right table tr, div#menu_top_right table td {
  padding:0px;
  margin:0px;
  vertical-align:center;
}

.menu_form {
  font-size:12px !important; font-size:10px;
  height:1.5em;
  padding:0px;
  margin:0px;
}
.menu_form_text {
  font-size:12px !important; font-size:11px;
  padding:0px;
  margin:0px;
}

.menu_form_button {
  font-size:12px !important; font-size:11px;
  padding:0px 5px;
  margin:1px;
}

.menu_form_dropdown {
  position:relative;
  top:0px !important; top:-3px;

}

p#extra_links {
  font-size:(DEFAULT_FONT_SIZE)px;
  font-weight:bold;
  display:inline; 
  float:right; 
  z-index:1; 
  top:-4px; 
  right:0px; 
  position:absolute; 
  padding-top:14px;
  margin-top:0px;
}



