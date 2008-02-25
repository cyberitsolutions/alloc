var alloc_http_request = new Array();

function makeAjaxRequest(url,entityid) {
  document.getElementById(entityid).innerHTML = '<img src="../images/ticker2.gif" alt="Updating field..." title="Updating field...">';
  jQuery.get(url,'',function(data) {
    document.getElementById(entityid).innerHTML = data;
  })
}

function set_grow_shrink_box(id, display, images, text, id_to_hide) {
  if (display == 'none') {
    display = 'inline';
    var display_to_hide = 'none';
    var image = images+'small_shrink.gif'
  } else {
    display = 'none';
    var display_to_hide = 'inline';
    var image = images+'small_grow.gif'
  }
  document.getElementById(id).style.display=display;
  if (id_to_hide) {
    document.getElementById(id_to_hide).style.display=display_to_hide;
  }
  var str = "<nobr><a class=\"growshrink nobr\" href=\"#\" onClick=\"return set_grow_shrink_box('"+id+"','"+display+"','"+images+"','"+text+"','"+id_to_hide+"');\">"+text+"<img border=\"0\" src=\""+image+"\"></a></nobr>"
  document.getElementById('button_'+id).innerHTML = str;
  return false;
}

function sidebyside_activate(id,arr) {
  if (id == "sbsAll") {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        document.getElementById(arr[i]).style.display='inline';
        document.getElementById('sbs_link_' + arr[i]).className = "sidebyside"
      }
    }
    document.getElementById('sbs_link_' + id).className = "sidebyside_active";

  } else {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        document.getElementById(arr[i]).style.display='none';
      }
      document.getElementById('sbs_link_' + arr[i]).className = "sidebyside"
    }
    document.getElementById('sbs_link_' + id).className = "sidebyside_active";
    document.getElementById(id).style.display='inline';
  }
}


// These global variables are for the setTimeout() below
var alloc_current_resizable_textarea = "";
var alloc_current_resizable_textarea_default_height = "";
var alloc_current_resizable_textarea_timer = "";

// this function dynamically resizes a text area as data is inputted
function adjust_textarea(textarea, default_height) {
  // a div is setup off screen, we use that div to determine the height of the textarea
  alloc_current_resizable_textarea = textarea;
  alloc_current_resizable_textarea_default_height = default_height;
  var shadow = document.getElementById("shadow_" + textarea.id);
  shadow.style.width=parseInt(textarea.clientWidth-8)+'px';
  shadow.innerHTML = textarea.value.replace(/[\n]/g,'<br />&nbsp;');
  var shadow_height = shadow.clientHeight;
  if(shadow_height < default_height) {
    var n = default_height;
  } else {
    var n = shadow_height+14;
  }
  textarea.style.height = n+'px';
  alloc_current_resizable_textarea_timer = setTimeout('adjust_textarea(alloc_current_resizable_textarea,alloc_current_resizable_textarea_default_height)', 1000);
}

function stop_textarea_timer() {
  clearTimeout(alloc_current_resizable_textarea_timer);
}

