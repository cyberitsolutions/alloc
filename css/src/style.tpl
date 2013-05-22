
/* 
 * CSS for allocPSA
 * Note: you'll occassionally see a CSS hack like: width:90% !important; width:95%;
 * Good web browsers obey the first declaration "90%", IE5/6 will obey the latter. 
 */

html {
  height:100%;
}

body { 
  (BODY_BACKGROUND); 
  font-family:arial,helvetica,sans-serif; 
  color:(BODY_COLOR); 
  padding:10px 30px;
  width:auto !important; width:95%;
  margin:0px;
  min-height:100%;
}

/* This should match the body font */
pre.comment {
  font-family:arial,helvetica,sans-serif;
  overflow:auto;
  white-space:pre-wrap;
  margin:0px;
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
  border:1px solid (BODY_BORDER);
  clear:both;
  text-align:left;
  margin-bottom:4px;
  -webkit-border-radius: 12px;
     -moz-border-radius: 12px;
          border-radius: 12px;
  -webkit-box-shadow:3px 2px 4px #999;
     -moz-box-shadow:3px 2px 4px #999;
          box-shadow:3px 2px 4px #999;
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
  text-transform:none;
}

a.sidebyside {
  padding:5px;
  background-color: (TABLE_BOX_BACKGROUND_COLOR);
  border:1px solid (SBS_BORDER_COLOR);
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

a.undecorated {
  text-decoration:none;
  color:(BODY_COLOR);
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
  color:(SMALL_HEADING_COLOR);
  border-bottom:1px solid (DECORATIVE_LINE_COLOR);
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

textarea, input, select, button { 
  font-size:100%; 
  font-family:arial,helvetica,sans-serif; 
  font-weight:normal;
  -webkit-box-sizing:border-box;
     -moz-box-sizing:border-box;
          box-sizing:border-box;
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
  list-style:disc outside none
}

form { 
  display:inline; 
  padding:0px; 
  margin:0px;
}
div#main input[type="submit"],
div#main input[type="button"],
div#main button {
  padding:4px 8px;
}
input[type="text"], 
input[type="password"], 
input[type="checkbox"], 
textarea,
select {
  border: 1px solid (DECORATIVE_LINE_COLOR);
  background-color: (FORM_ELEMENT_BACKGROUND);
  text-transform:none; /* list.th turns options into uppercase */
}
input[type="text"]:focus, 
input[type="password"]:focus, 
input[type="checkbox"]:focus, 
textarea:focus,
select:focus {
  border: 1px solid (FORM_ELEMENT_ACTIVE_BORDER_COLOR);
  background-color: (FORM_ELEMENT_ACTIVE_BACKGROUND);
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
button {
  text-shadow:#fff 0px 1px 0px;
  text-transform:none !important;
  -moz-box-shadow:2px 2px 3px #ccc;
  -webkit-box-shadow:2px 2px 3px #ccc;
  box-shadow:2px 2px 3px #ccc;
  outline: 0;
  -moz-border-radius: 12px;
  -webkit-border-radius: 12px;
  border-radius: 12px;
  border: 1px solid #999;
  background-color:#fff;
  font-weight:bold;
  color:#333;
}

button:hover {
  color:#666;
}

button:active {
  -moz-box-shadow:0px 0px 0px #bbbbbb;
  -webkit-box-shadow:0px 0px 0px #bbbbbb;
  box-shadow:0px 0px 0px #bbbbbb;
  outline: none;
  background-color:#ccc;
}

button::-moz-focus-inner {
  border: 0;
  outline: none;
}

button.delete_button:hover {
  color:#dc0606;
}
button.save_button:hover {
  color:#10ae00;
}
button.filter_button:hover {
  color:#ef8849;
}

button.delete_button:active {
  background-color:#ff9797 !important;
}
button.save_button:active {
  background-color:#99e791 !important;
}
button.filter_button:active {
  background-color:#ffd3a0 !important;
}


button.delete_button {
  border: 1px solid #dc0606;
}
button.save_button {
  border: 1px solid #10ae00;
}
button.filter_button {
  border: 1px solid #ffac4b;
}


label.radio {
  background:white;
  border:1px solid #999;
  padding:8px;
}
label.radio:hover {
  background-color:(HIGHLIGHTED_BACKGROUND_COLOR);
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
.right a {
  margin-left:4px;
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
.top {
  vertical-align:top;
}


#helper { 
  position:absolute; 
  z-index:1000;
  background-color:(HELP_BACKGROUND);
  border:1px solid (DECORATIVE_LINE_COLOR);
  color:(HELP_COLOR);
  width:400px; 
  font-size:100%; 
  padding:8px;
}
.help_button {
  border:1px solid (HELP_ICON_BORDER_COLOR);
  vertical-align:sub !important; vertical-align:bottom;
}
.help_button:hover {
  border:1px solid (DECORATIVE_LINE_COLOR);
}

.calendar_container {
  position:relative; 
  vertical-align:middle; 
  padding:0px; 
  margin:0px; 
  display:inline; 
}
.calendar_container img {
  vertical-align:text-bottom;
  cursor:pointer; 
  border:1px solid (CALENDAR_IMAGE_BORDER_COLOR);
  margin:0px; 
  padding:0px; 
  position:relative;
  top:0px !important; top:-2px;
}
.calendar_container img:hover {
  border: 1px solid (CALENDAR_ACTIVE_BORDER_COLOR);
  background-color:(CALENDAR_ACTIVE_BACKGROUND);
}
.calendar_container input {
  vertical-align:text-bottom;
}

.message { 
  padding:8px 4px; 
  margin:15px auto 0px auto; 
  border:1px solid (DECORATIVE_LINE_COLOR);
  background-color:(HELP_BACKGROUND);
  z-index:1000;
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
  color:(TABLE_BOX_TH_COLOR); 
  font-size:110%; 
  padding:3px;
  font-weight:normal;
  (TABLE_BOX_TH_BACKGROUND); 
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

table.box th.header > span {
  float:right;
}
table.box th.header span a {
  margin-left:0.2em;
  font-size:90%;
}
table.box th.header b {
  font-weight:normal;
  font-size:90%;
}

table.box th.header span a.star b {
  font-size:110%;
  top:2px;
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
  background-color:(CALENDAR_TODAY_BACKGROUND);
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
  background-color:(CALENDAR_ABSENT_BACKGROUND);
  color:(CALENDAR_ABSENT_COLOR);
}
table.alloc_calendar td.absent h1{
  color:(CALENDAR_ABSENT_COLOR);
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
table.alloc_calendar td.selected {
  background-color:(PANEL_HARPO_BG_COLOR);
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

.hover:hover {
  background-color:(HIGHLIGHTED_BACKGROUND_COLOR); 
}

table.nopad, table.nopad td, table.nopad th {
  margin:0px !important;
  padding:0px !important;
  border-collapse:collapse !important;
}

img.taskType {
  margin:0px 2px;
  padding:0px;
}

.corner {
  -webkit-border-radius: 12px;
     -moz-border-radius: 12px;
          border-radius: 12px;
  border-collapse:separate; /* to prevent border-collapse:collapse being inherited */
}

.shadow {
  -moz-box-shadow:4px 4px 5px #bbbbbb;
  -webkit-box-shadow:4px 4px 5px #bbbbbb;
  box-shadow:4px 4px 5px #bbbbbb;
}
.shadow2 {
  -moz-box-shadow:1px 1px 5px #bbbbbb;
  -webkit-box-shadow:1px 1px 5px #bbbbbb;
  box-shadow:1px 1px 5px #bbbbbb;
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
  background-color:(HIGHLIGHTED_BACKGROUND_COLOR) !important; 
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
table.list tr:last-child th {
  font-size:100%;
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

a.star {
  font-size:130% !important;
  text-decoration:none !important;
  color:#ccc !important;
}
a.star:hover, a.hot {
  color:#f1965f !important;
  cursor: pointer;
}

/* .panel tables (Harpo) have a blue border and a light blue background in the default theme.
   When a .panel table is inside another .panel table (Chico), the border and the background 
   are lighter.

   When a table has .panel and .loud (Groucho), then the table has an orange border with a light
   orange background. And correspondingly, any inner .panel's (Zeppo) render more lightly.
*/

.panel {
  border:1px solid (PANEL_HARPO_FRAME_COLOR); 
  background:(PANEL_HARPO_BG_COLOR); 
  margin-bottom:10px; 
  margin-top:10px;
  margin-left:10px;
  margin-right:10px;
  padding:8px;
}

.panel div.hidden_text {
  color:(COMMENT_HIDDEN_TEXT_COLOR);
  font-style:italic;
}
.loud {
  border:1px solid (PANEL_GROUCHO_FRAME_COLOR); 
  background:(PANEL_GROUCHO_BG_COLOR); 
}
.panel .panel {
  border:1px solid (PANEL_CHICO_FRAME_COLOR); 
  background:(PANEL_CHICO_BG_COLOR); 
  margin-top:3px; 
  margin-bottom:8px; 
  margin-left:0px;
  margin-right:0px;
  -webkit-box-shadow: 2px 2px 2px rgba(0,0,0,0.4);
     -moz-box-shadow: 2px 2px 2px rgba(0,0,0,0.4);
   -opera-box-shadow: 2px 2px 2px rgba(0,0,0,0.4);
          box-shadow: 2px 2px 2px rgba(0,0,0,0.4);
}
.loud .panel {
  border:1px solid (PANEL_ZEPPO_FRAME_COLOR); 
  background:(PANEL_ZEPPO_BG_COLOR); 
}
.panel th { 
  text-align:left; 
  background:(PANEL_HARPO_BG_COLOR); 
  vertical-align:top; 
  color:(TD_COLOR); 
  font-weight:normal; 
  border:0px solid black;
  text-transform:none; /* list.th turns these uppercase */
  font-size:100%;
}
.loud th {
  background:(PANEL_GROUCHO_BG_COLOR); 
}
.panel td { 
  background:(PANEL_HARPO_BG_COLOR);
  border:0px solid black;
}
.quiet {
  border: 1px solid #e0e0e0;
}
.quiet, .quiet td, .quiet a { 
  background:#f9f9f9;
  color:#aaaaaa;
}
.loud td {
  background:(PANEL_GROUCHO_BG_COLOR);
}
.panel td .panel { 
  background:(PANEL_CHICO_BG_COLOR); 
}
.loud td .panel { 
  background:(PANEL_ZEPPO_BG_COLOR); 
}
.panel td .panel th { 
  background:(PANEL_CHICO_BG_COLOR);
}
.loud td .panel th { 
  background:(PANEL_ZEPPO_BG_COLOR);
}
.panel td .panel td { 
  background:(PANEL_CHICO_BG_COLOR);
}
.loud td .panel td { 
  background:(PANEL_ZEPPO_BG_COLOR);
}

.hidden-links a.config-link {
  color:#ccc !important;
  float:right;
  visibility:visible;
  font-size:80% !important;
}
.hidden-links a.config-link:hover {
  color:#333 !important;
  visibility:visible;
}
.config-pane {
  background-color:#fff;
  min-width:400px;
  padding:20px;
  border:1px solid #ddd;
  z-index:100;
  font-size:80%;
}
.config-pane h6 {
  margin-top:10px;
  margin-bottom:3px;
}

.faint {
  color:(COMMENT_HIDDEN_TEXT_COLOR);
  font-style:normal;
}

.warn { 
  color:(WARN_COLOR);
}
.pending { 
  background-color:(PANEL_HARPO_BG_COLOR); 
  border:1px solid (PANEL_HARPO_FRAME_COLOR); 
}
div.warn { 
  background-color:(WARN_BG_COLOR); 
  border:1px solid (WARN_FRAME_COLOR); 
}
.approved { 
  background-color:(APPROVED_BG_COLOR); 
  border:1px solid (APPROVED_FRAME_COLOR); 
}
.rejected { 
  background-color:(REJECTED_BG_COLOR); 
  border:1px solid (REJECTED_FRAME_COLOR); 
}

.pending table td, .warn table td, .approved table td, .rejected table td {
  background-color:transparent !important;
}


#menu {
  margin:0px;
  padding:0px;
  width:100%; 
  position:relative; 
  top:0px;
  margin-top:11px;
  margin-bottom:10px;
  color:(TABLE_MENU_A_COLOR);
}

#menu td {
  color:(TABLE_MENU_TD_COLOR);
}

div#tabs {
  margin:0px;
  padding:0px !important; padding:0px 16px;
  clear:both;
  text-align:left;
  position:relative;
  left:1px;
  z-index:1;
}

/* A single image based tab */
#tabs a.tab {
  float:left;
  position:absolute;
  display:inline;
  width:68px;
  text-align:center;
          border-radius: 8px 8px 0px 0px;
     -moz-border-radius: 8px 8px 0px 0px;
  -webkit-border-radius: 8px 8px 0px 0px;
  (DIV_TAB_BACKGROUND)
  padding:6px 0px 6px 0px;
}

#tabs a.tab:hover {
  background-color:#f9f9f9;
}

/* Active tabs use this class as well */
#tabs a.active {
  background-color:white;
  border-bottom:0px !important;
  border:1px solid #999;
  padding:6px 0px 7px 0px;
}

#tabs a.active:hover {
  background-color:#fff !important;
}

#tabs a.tab {
  text-decoration:none;
}

#tabs a.tab:link    { color:(TABLE_MENU_A_COLOR);  }
#tabs a.tab:visited { color:(TABLE_MENU_A_COLOR);  }
#tabs a.tab:hover   { color:(TABLE_MENU_A_HOVER_COLOR);}

p#extra_links a:link    { color:(TABLE_MENU_EXTRA_A_COLOR);  }
p#extra_links a:visited { color:(TABLE_MENU_EXTRA_A_COLOR);  }
p#extra_links a:hover   { color:(TABLE_MENU_EXTRA_A_HOVER_COLOR);}

p#extra_links a:link,
p#extra_links a:visited,
p#extra_links a:hover {
  text-decoration:none;
  position:relative;
  padding: 5px 0px;
}

#tabs a.tab:link, #tabs a.active:visited {
  color:(TABLE_MENU_A_ACTIVE_COLOR);
}

p#extra_links {
  font-weight:normal;
  z-index:-1; 
  position:relative; 
  float:right;
  right:0px !important; right:-16px; 
  padding:0px !important; padding:0px 16px;
  height:27px;
  margin:0px;
  clear:both;
}

div.grippie {
  background:#eeeeee Url(../images/grippie.png) no-repeat scroll center 2px;
  border-color:#dddddd;
  border-style:solid;
  border-width:0pt 1px 1px;
  cursor:s-resize;
  height:9px;
  width:100% !important; width:95%;
  overflow:hidden;
  margin-bottom:5px;
  -webkit-box-sizing:border-box;
     -moz-box-sizing:border-box;
          box-sizing:border-box;
}
div.grippie:hover {
  background:#fffae8 url(../images/grippie.png) no-repeat scroll center 2px;
}
/* IE fix */
.resizable-textarea textarea {
  display:block;
  margin-bottom:0pt;
  width:100% !important; width:95%;
  height: 20%;
  padding-left:0px;
  padding-right:0px;
}


/* CSS for the login form */

body#login {
  background-color:#eeeeee;
  background-image:none;
  padding-top:40px;
}

body#login .width {
  width:300px;
  margin: 30px auto;
  padding:15px;
}

body#login .whitely {
  border:1px solid #cccccc;
  background-color:#ffffff;
}

body#login #links {
  background: transparent url(../images/roundbg.png) no-repeat bottom left;
  text-align:right;
  padding-bottom:30px;
  margin-bottom:35px;
}

body#login span {
  display:inline-block;
  text-align:right;
  white-space:nowrap;
  width:37%;
  margin:5px;
}

body#login input[type=text], body#login input[type=password] {
  width:100%;
}

body#login #footer {
  color:#999999;
  text-align:center;
  font-size:80%;
}

body#login #slowest_query_1,
body#login #slowest_query_2,
body#login #all_page_queries_1,
body#login #all_page_queries_2 {
  display:none;
}

body.obfus .obfuscate {
  color:#e0e0e0 !important;
  background-color:#e0e0e0 !important;
}

body.obfus .obfuscate:hover {
  color:(BODY_COLOR) !important;
  background-color:transparent !important;
}

.selectn-label {
  border:1px solid #ccc;
  background-color:#fff;
  height:1.2em;
  display:inline-block;
  position:relative;
  white-space:nowrap;
  font-size:1em;
  overflow:hidden;
  cursor:default;
  color:(BODY_COLOR);
  text-align:left;
}
.selectn-label img {
  position:absolute;
  top:2px;
  right:0px;
}
.selectn-dropdown {
  position:absolute;
  z-index:1000;
  border:1px solid #333;
  background-color:#fff;
  display:none;
  text-align:left;
  max-width:500px;
  max-height:500px;
  overflow-y:auto;
}
.selectn-search {
  width:60px;
  padding:2px 8px;
  border:1px solid #999;
  outline: 0;
  -webkit-border-radius: 12px;
     -moz-border-radius: 12px;
          border-radius: 12px;
  border-collapse:separate; /* to prevent border-collapse:collapse being inherited */
}

.selectn-dropdown label {
  display:block;
  white-space:nowrap;
  padding-right:5px;
  color:(BODY_COLOR);
  border-top:1px solid #fff;
  border-bottom:1px solid #fff;
  vertical-align:center;
}
.selectn-dropdown label:hover, .selectn-button:hover, .selectn-dropdown label.hover {
  background-color:#f4f4f4;
}
.selectn-button {
  font-size:75%;
  -webkit-box-shadow:0px 0px 0px #fff;
     -moz-box-shadow:0px 0px 0px #fff;
          box-shadow:0px 0px 0px #fff;
  margin:0px !important;
  padding:4px 8px !important;
  -webkit-border-radius: 12px;
     -moz-border-radius: 12px;
          border-radius: 12px;
  border-collapse:separate; /* to prevent border-collapse:collapse being inherited */
} 
.selectn-active {
  border:1px solid #333;
}
.selectn-cb-selected {
  background-color:#d6f2ff !important;
  border-top:1px solid #abd7ec !important;
  border-bottom:1px solid #abd7ec !important;
}
.selectn-cb-selected + .selectn-cb-selected {
  border-top:1px solid #d6f2ff !important; /* to avoid double-borders on adjacent selections */
}
.selectn-buttons {
  white-space:nowrap;
  margin:4px;
}

