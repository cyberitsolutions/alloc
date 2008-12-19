
/* 
 * CSS for allocPSA
 * Note: you'll occassionally see a CSS hack like: width:90% !important; width:95%;
 * Good web browsers obey the first declaration "90%", IE5/6 will obey the latter. 
 */

body { 
  (BODY_BACKGROUND); 
  font-family:arial,helvetica,sans-serif; 
  color:(BODY_COLOR); 
  padding:0px;
  width:auto !important; width:95%;
  margin:0px 14px;
}

/* Need this for IE 5, so that the font sizes within tables are not ridiculously huge */
body table {
  font-size:100%;
}

/* Setup the main content box that encapsulates everything */
div#main {
  width:100%;
  margin:0px;
  padding:0px 0px 14px 0px !important; padding:0px 16px;
  background-color:white;
  border:1px solid #9c9c9c;
  position:relative;
  clear:both;
  text-align:left;
  margin-bottom:4px;
}

/* Hack to ensure certain div encapsulate all the stuff inside them */
div#main:after, .edit:after, .view:after, .enclose:after, .wikidoc:after {
  content:"";
  display:block;
  height:0;
  clear:both;
}

div#main2 {
  margin:0px 1% !important; margin:auto;
}

a:link, a:visited { 
  color:(A_COLOR);
}
a:hover { 
  color:(A_HOVER); 
}

a.growshrink, a.magic {
  color:(TD_COLOR);
  text-decoration:none;
  border-bottom:1px dashed (BODY_COLOR);
  text-transform:none;
}
a.growshrink:hover, a.magic:hover {
  border-bottom:1px solid (BODY_COLOR);
}

a.sidebyside {
  padding:5px;
  background-color: #f9f9f9;
  border:1px solid #cccccc;
  text-decoration:none;
  color:(TABLE_TASKS_TH_COLOR);
  font-size:100%; 
}
a.sidebyside:hover {
  background:(TABLE_TASKS_TH_BACKGROUND);
  color:(TABLE_TASKS_TH_COLOR);
  border:1px solid (TABLE_TASKS_TH_COLOR);
}
a.sidebyside_active {
  background:(TABLE_TASKS_TH_BACKGROUND);
  color:(TABLE_TASKS_TH_COLOR);
  border:1px solid (TABLE_TASKS_TH_COLOR);
  text-decoration:none;
  padding:5px;
  font-size:100%; 
}

h1 { 
  font-weight:bold; 
  font-size:150%; 
}
h2 { 
  font-weight:normal; 
  font-size:140%; 
  clear:both;
}
h3 { 
  font-weight:bold; 
  font-size:130%; 
}
h4 { 
  font-weight:bold; 
  font-size:120%; 
}
/* These are the little headers on the task page */
h6 {
  position:relative;
  font-size:90%;
  font-weight:normal;
  text-transform:uppercase;
  color:#bbbbbb;
  border-bottom:1px solid #cccccc;
  padding:0px;
  margin:20px 0px 10px 0px;
  clear:both;
}

/* For having a second column in the h6 */
h6 div {
  width:50%;  
  float:right;
  display:inline;
  position:absolute;
  top:0px;
  right:0px !important; right:12px;
}

textarea, input, select, textarea { 
  font-size:100%; 
  font-family:arial,helvetica,sans-serif; 
  font-weight:normal;
}
th { 
  color:(TD_COLOR); 
  text-align:left; 
  font-size:100%; 
}
td { 
  color:(TD_COLOR); 
  font-size:100%; 
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
input[type="text"], 
input[type="password"], 
input[type="checkbox"], 
textarea,
select {
  border: 1px solid #cccccc; 
  background-color: #fcfcfc;
  text-transform:none; /* list.th turns options into uppercase */
}
input[type="text"]:focus, 
input[type="password"]:focus, 
input[type="checkbox"]:focus, 
textarea:focus,
select:focus {
  border: 1px solid #666666; 
  background-color: #ffffff;
}
input[type="text"].bad,
input[type="password"].bad,
input[type="checkbox"].bad,
textarea.bad,
select.bad,
input[type="text"]:focus.bad,
input[type="password"]:focus.bad,
input[type="checkbox"]:focus.bad,
textarea:focus.bad,
select:focus.bad
{
  border:1px solid #cd7373;
  background-color: #ffffff;
}
input[readonly="true"], input[readonly="true"]:focus {
  border:1px solid #e0e0e0;
  background-color:(TABLE_BOX_BACKGROUND_COLOR);
  color:#777777;
}

hr {
  border:0px;
  width: 100%;
  color: #ccc;
  background-color: #ccc;
  height:1px;
}

.right { 
  text-align:right; 
} 
.left { 
  text-align:left; 
} 
.small {
  padding:0px;
  margin:0px;
}
.center { 
  text-align:center; 
} 
.nobr { 
  white-space:nowrap 
}
.hidden, .edit {
  display:none;
}
.edit, .view {
  clear:both;
}
.padded {
  padding:8px;
}
.bold {
  font-weight:bold;
}
.inline {
  display:inline;
}


#helper { 
  position:absolute; 
  z-index:2;  
  background-color:#fffdf2; 
  border:1px solid #cccccc; 
  color:#666666; 
  width:400px; 
  font-size:100%; 
  padding:8px;
}
.help_button {
  border:1px solid #999999;
  vertical-align:sub !important; vertical-align:bottom;
}
.help_button:hover {
  border:1px solid #cccccc;
}

.calendar_container {
  position:relative; 
  vertical-align:middle; 
  padding:0px; 
  margin:0px; 
  display:inline; 
  float:left;
}
.calendar_container img {
  vertical-align:text-bottom;
  cursor:pointer; 
  border:1px solid #999999; 
  margin:0px; 
  padding:0px; 
  position:relative;
  top:0px !important; top:-2px;
}
.calendar_container img:hover {
  border: 1px solid #666666;
  background-color:#999999;
}
.calendar_container input {
  vertical-align:text-bottom;
}

.message { 
  padding:0px 4px; 
  margin:15px auto 0px auto; 
  border:1px solid #cccccc;
  background-color:#fffdf2; 
}
.bad { 
  color:(TRANSACTION_REJECTED); 
  font-size:100%; 
}
.good { 
  color:(TRANSACTION_APPROVED); 
  font-size:100%; 
}
.help { 
  color:(TRANSACTION_PENDING);  
  font-size:100%;
}


/* Main content boxes */ 
table.box { 
  border:(TABLE_BOX_BORDER); 
  background-color:(TABLE_BOX_BACKGROUND_COLOR); 
  vertical-align:top; 
  width:100%; 
  margin:14px 0px 0px 0px;
  border-collapse:collapse;
  border-spacing:0px;
} 
table.box th { 
  (TABLE_BOX_TH_BACKGROUND); 
  color:(TABLE_BOX_TH_COLOR); 
  font-size:110%; 
  border-bottom:1px solid #e0e0e0;
  padding:3px;
  font-weight:normal;
} 
table.box th.center { 
  text-align:center; 
} 
table.box th.right table { 
  width:40%; 
} 
table.box th a:link, table.box th a:visited { 
  font-weight:normal; 
  color:(TABLE_BOX_TH_A_LINK_COLOR); 
}
table.box th a:hover { 
  font-weight:normal; 
  color:(TABLE_BOX_TH_A_HOVER_COLOR); 
}
table.box td {
  padding:3px;
}


/* Calendar */
table.alloc_calendar { 
  width:100%; 
  border-right:1px solid (PANEL_HARPO_FRAME_COLOR); 
  border-bottom:1px solid (PANEL_HARPO_FRAME_COLOR);
} 
table.alloc_calendar td { 
  background-color:(TABLE_BOX_BACKGROUND_COLOR); 
  border-left:1px solid (PANEL_HARPO_FRAME_COLOR);  
  border-top:1px solid (PANEL_HARPO_FRAME_COLOR); 
  width:10%; vertical-align:top;
}
table.alloc_calendar td.today { 
  background-color:#fffdf2; 
  border-top:2px solid (PANEL_HARPO_FRAME_COLOR);  
  border-left:2px solid (PANEL_HARPO_FRAME_COLOR);  
  border-right:1px solid (PANEL_HARPO_FRAME_COLOR);  
  border-bottom:1px solid (PANEL_HARPO_FRAME_COLOR); 
}
table.alloc_calendar td.today h1 {
  font-weight:bold;
}
table.alloc_calendar td.even { 
  background-color:(TR_ODD_BACKGROUND_COLOR); 
}
table.alloc_calendar td.absent {
  background-color:#cccccc;
  color:#999999;
}
table.alloc_calendar td.absent h1{
  color:#999999;
}
table.alloc_calendar h1 {
  text-align:right;
  font-size:100%;
  margin:0px;
  padding:3px;
  font-weight:normal;
}
table.alloc_calendar img {
  float:left;
  margin:0px;
}
table.alloc_calendar td:hover { 
  background-color:(TR_HOVER_BACKGROUND_COLOR); 
}


/* Filters */
table.filter { 
  background-color:(PANEL_HARPO_BG_COLOR); 
  border:1px solid (PANEL_HARPO_FRAME_COLOR); 
  padding:4px; 
  margin-top:4px; 
  text-align:left;
}

table.filter td {
  color:(TABLE_TASKS_TH_COLOR); 
}

.filter table td { 
  color:(TABLE_TASKS_TH_COLOR); 
}
.filter table th { 
  font-weight:bold; 
  background-color:(PANEL_HARPO_BG_COLOR); 
  color:(TD_COLOR); 
  border-bottom:0px solid #ffffff; 
  text-align:center; 
}

tr.odd { 
  background-color:(TR_ODD_BACKGROUND_COLOR); 
}
tr.even { 
  background-color:(TR_EVEN_BACKGROUND_COLOR); 
}
tr.odd:hover, tr.even:hover {
  background-color:(TR_HOVER_BACKGROUND_COLOR); 
}

img.taskType {
  margin:0px 2px;
  padding:0px;
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

table.list { 
  width:100%; 
  border-left:1px solid (PANEL_HARPO_FRAME_COLOR); 
  border-right:1px solid (PANEL_HARPO_FRAME_COLOR); 
  border-bottom:1px solid (PANEL_HARPO_FRAME_COLOR);
  border-collapse:collapse;
  border-spacing:0px;
}
td.list { 
  background-color: (PANEL_HARPO_BG_COLOR); 
  padding:4px; 
}
table.list th, table.alloc_calendar th { 
  border-bottom:0px; 
  font-weight:normal; 
  white-space:nowrap; 
  background:(TABLE_TASKS_TH_BACKGROUND); 
  color:(TABLE_TASKS_TH_COLOR); 
  font-size:90%;
  text-transform:uppercase;
}
table.list td, table.list th, table.alloc_calendar th { 
  border-top:1px solid (PANEL_HARPO_FRAME_COLOR); 
  padding:4px;
}
table.alloc_calendar th { 
  border-left:1px solid (PANEL_HARPO_FRAME_COLOR); 
}
.sort_arrows {
  padding:0px 3px;
}
table.list td.grand_total, .grand_total { 
  border-top:1px solid black;
}

/* .panel tables (Harpo) have a blue border and a light blue background in the default theme.
   When a .panel table is inside another .panel table (Chico), the border and the background 
   are lighter.

   When a table has .panel and .loud (Groucho), then the table has an orange border with a light
   orange background. And correspondingly, any inner .panel's (Zeppo) render more lightly.
*/
table.panel div.hidden_text {
  color:#888888;
  font-style:italic;
}
table.panel { 
  border:1px solid (PANEL_HARPO_FRAME_COLOR); 
  background:(PANEL_HARPO_BG_COLOR); 
  margin-bottom:5px; 
  margin-top:0px;
}
table.loud {
  border:1px solid (PANEL_GROUCHO_FRAME_COLOR); 
  background:(PANEL_GROUCHO_BG_COLOR); 
}
table.panel table.panel {
  border:1px solid (PANEL_CHICO_FRAME_COLOR); 
  background:(PANEL_CHICO_BG_COLOR); 
  margin-bottom:6px; 
}
table.loud table.panel {
  border:1px solid (PANEL_ZEPPO_FRAME_COLOR); 
  background:(PANEL_ZEPPO_BG_COLOR); 
}
table.panel th { 
  text-align:left; 
  background:(PANEL_HARPO_BG_COLOR); 
  vertical-align:top; 
  color:(TD_COLOR); 
  font-weight:normal; 
  padding:4px; 
  border:0px solid black;
  text-transform:none; /* list.th turns these uppercase */
  font-size:100%;
}
table.loud th {
  background:(PANEL_GROUCHO_BG_COLOR); 
}
table.panel td { 
  background:(PANEL_HARPO_BG_COLOR);
  padding:6px; 
  border:0px solid black;
}
table.loud td {
  background:(PANEL_GROUCHO_BG_COLOR);
}
table.panel td table.panel { 
  background:(PANEL_CHICO_BG_COLOR); 
}
table.loud td table.panel { 
  background:(PANEL_ZEPPO_BG_COLOR); 
}
table.panel td table.panel th { 
  background:(PANEL_CHICO_BG_COLOR);
}
table.loud td table.panel th { 
  background:(PANEL_ZEPPO_BG_COLOR);
}
table.panel td table.panel td { 
  background:(PANEL_CHICO_BG_COLOR);
}
table.loud td table.panel td { 
  background:(PANEL_ZEPPO_BG_COLOR);
}



.faint {
  color:#888888;
  font-style:normal;
}

.warn { 
  color:(WARN_COLOR);
}
table.pending { 
  background-color:(PANEL_HARPO_BG_COLOR); 
  border:1px solid (PANEL_HARPO_FRAME_COLOR); 
}
table.warn { 
  background-color:(WARN_BG_COLOR); 
  border:1px solid (WARN_FRAME_COLOR); 
}
table.approved { 
  background-color:(APPROVED_BG_COLOR); 
  border:1px solid (APPROVED_FRAME_COLOR); 
}
table.rejected { 
  background-color:(REJECTED_BG_COLOR); 
  border:1px solid (REJECTED_FRAME_COLOR); 
}

#menu {
  margin:0px;
  padding:0px;
  width:100%; 
  position:relative; 
  top:0px;
  margin-bottom:20px;
}

#menu table {
  position:relative;
  top:10px;
  margin:0px;
  padding:0px;
  text-align:left;
  font-size:12px; /* hardcoded */
  font-weight:bold;
  color:(TABLE_MENU_A_COLOR);
}

#menu select {
  position:relative;
  top:0px !important; top:-2px;
}

div#tabs {
  width:100%;
  margin:0px;
  padding:0px !important; padding:0px 16px;
  border:1px;
  clear:both;
  text-align:left;
  height:27px;
  position:relative;
  top:1px;
  left:1px;
  z-index:1;
}

/* A single image based tab */
div.tab {
  float:left;
  position:absolute;
  background:url(../images/tab_unselected.gif) top no-repeat;
  display:inline;
  width:80px;
  height:27px;
  text-align:center;
  font-size:12px; /* hardcoded */
}

/* Active tabs use this class as well */
div.active {
  background:url(../images/tab_selected.gif) top no-repeat;
}

div.tab a {
  font-weight:bold;
  text-decoration:none;
  position:relative;
  top:8px !important; top:7px;
  padding: 5px 10px;
}

div.tab a:link, p#extra_links a:link        { color:(TABLE_MENU_A_COLOR);  }
div.tab a:visited, p#extra_links a:visited  { color:(TABLE_MENU_A_COLOR);  }
div.tab a:hover, p#extra_links a:hover      { color:(TABLE_MENU_A_HOVER_COLOR);}

div.active a:link, div.active a:visited {
  color:(TABLE_MENU_A_ACTIVE_COLOR);
}

p#extra_links {
  font-size:12px; /* hardcoded */
  font-weight:bold;
  display:inline; 
  z-index:-1; 
  position:relative; 
  float:right;
  right:0px !important; right:-16px; 
  top:9px; 
  padding:0px;
  margin:0px;
}


