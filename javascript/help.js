function help_text_on(img, str) {
  delay(150);
  popup(str); 
  x = findPosX(document.getElementById(img.id)) +17 -400;
  if (x < 0) {
    x = x + 380;
  }
  helper.style.left=x+'px';
  y = findPosY(document.getElementById(img.id)) +22 + yyy;
  r = 1600;
  if (y > r) {
    y = y - document.getElementById("helper_table").offsetHeight-24;
  }
  helper.style.top=y+'px';
  toggleCombos("hidden"); 
  img.style.border = "1px solid #cccccc";
}
function help_text_off(img) {
  yyy=-1000;
  helper.style.display="none";
  toggleCombos("visible"); 
  img.style.border = "1px solid #999999";
}
function popup(msg) {
  var content;
  content ="<table width='150' border='0' cellpadding='4' cellspacing='0' id='helper_table' class='helper_table'><tr><td>";
  content += msg + "</td></tr></table>";
  yyy = 0;
  document.getElementById("helper").innerHTML=content;
  helper.style.display='';
}
function toggleCombos(showHow){
  var i, j, size;
  var hlp_x1,hlp_y1,hlp_x2,hlp_y2;
  var cbo_x1,cbo_y1,cbo_x2,cbo_y2;
  for (i=0;i<document.forms.length;i++) {
    for (j=0;j<document.forms[i].elements.length;j++) {
      if (document.forms[i].elements[j].tagName == "SELECT" && document.forms[i].name != "spiffyCal") {
        hlp_x1 = findPosX(document.getElementById("helper"));
        hlp_y1 = findPosY(document.getElementById("helper"));
        hlp_x2 = hlp_x1+400;
        hlp_y2 = hlp_y1+150;
        cbo_x1 = findPosX(document.forms[i].elements[j]);
        cbo_y1 = findPosY(document.forms[i].elements[j]);
        cbo_x2 = cbo_x1+222;
        size   = document.forms[i].elements[j].size ? document.forms[i].elements[j].size : 1;
        cbo_y2 = cbo_y1+(15*size);

        if (showHow == "visible") {
          document.forms[i].elements[j].style.visibility="visible";
        }

        if ((hlp_x1 <= cbo_x1 && cbo_x1 <= hlp_x2   &&  hlp_y1 <= cbo_y1 && cbo_y1 <= hlp_y2)
        ||  (hlp_x1 <= cbo_x2 && cbo_x2 <= hlp_x2   &&  hlp_y1 <= cbo_y2 && cbo_y2 <= hlp_y2)
        ||  (hlp_x1 <= cbo_x1 && cbo_x1 <= hlp_x2 && hlp_y1 <= cbo_y2 && cbo_y2 <= hlp_y2)) {
          document.forms[i].elements[j].style.visibility=showHow;
        }
      }
    }
  }
}
function findPosX(obj) {
  var curleft = 0;
  if (obj.offsetParent) {
    while (1) {
      curleft+=obj.offsetLeft;
      if (!obj.offsetParent) {
        break;
      }
      obj=obj.offsetParent;
    }
  } else if (obj.x) {
    curleft+=obj.x;
  }
  return curleft;
}
function findPosY(obj) {
  var curtop = 0;
  if (obj.offsetParent) {
    while (1) {
      curtop+=obj.offsetTop;
      if (!obj.offsetParent) {
        break;
      }
      obj=obj.offsetParent;
    }
  } else if (obj.y) {
    curtop+=obj.y;
  }
  return curtop;
}
function delay(gap) {
  var then=0, now=0;
  then = new Date().getTime();
  now = then;

  while ((now-then)<gap) {
    now = new Date().getTime();
  }
}

