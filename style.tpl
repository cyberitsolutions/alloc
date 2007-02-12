body { 
  (BODY_BACKGROUND); 
  font-family:arial,helvetica,sans-serif; 
  color:(BODY_COLOR); 
  margin:0px;
  padding:0px;
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

.hidden {
  display:none;
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
  background-color:(TR_ODD_BACKGROUND_COLOR); 
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
  background-color:(TR_ODD_BACKGROUND_COLOR); 
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
  padding:2px 4px; 
}


#all {
  width:auto;
  margin:0px 14px;
  text-align:center;
}

table#menu_top {
  margin:0px;
  padding:0px;
  margin-bottom:22px;
  text-align:left;
  position:relative;
  top:10px;
  font-size:(DEFAULT_FONT_SIZE)px;
  font-weight:bold;
  color:(TABLE_MENU_A_COLOR);
  width:100% !important; width:97%;
  display:inline;
}

table#menu_top th {
  text-align:left;
  font-size:100%;
}

table#menu_top td {
  text-align:right;
}

table#menu_top .menu_form_text {
  font-size:12px !important; font-size:13px;
  padding:0px;
  margin:0px;
}

table#menu_top .menu_form_button {
  font-size:11px !important; font-size:11px;
  padding:0px 2px;
  margin:1px;
}

table#menu_top .menu_form_select {
  position:relative;
  top:0px !important; top:-2px;
}


div#tabs {
  margin:0px;
  clear:both;
  text-align:left;
  height:27px;
  z-index:1;
  position: relative;
  top:0px;
  width:100%;
  left:1px !important; left:0px;
}

/* Setup the main content box that encapsulates everything */
div#main {
  margin:0px;
  padding-bottom:20px;
  background-color: white;
  border:1px solid #9c9c9c;
  clear:both;
  text-align:left;
  position: relative;
  top:-12px !important; top:-1px;
  width:100%
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
div.tab a:hover         { color:(TABLE_MENU_A_HOVER_COLOR); text-decoration:underline;}

div.active a:link, div.active a:visited {
  color:(TABLE_MENU_A_ACTIVE_COLOR);
  text-decoration:underline;
}

p#extra_links {
  font-size:(DEFAULT_FONT_SIZE)px;
  font-weight:bold;
  display:inline; 
  z-index:1; 
  position:relative; 
  float:right;
  right:0px; 
  top:-2px !important; top:9px; 
}



