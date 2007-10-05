var http_request = new Array();

function makeAjaxRequest(url,actionFunction,number,entityid) {
    http_request[number] = false;

    if (window.XMLHttpRequest) { // Mozilla, Safari,...
        http_request[number] = new XMLHttpRequest();
        if (http_request[number].overrideMimeType) { http_request[number].overrideMimeType('text/xml'); }

    } else if (window.ActiveXObject) { // IE
        try { 
          http_request[number] = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try { 
              http_request[number] = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
            }
        }
    }

    if (!http_request[number]) {
        // alert('Giving up :( Cannot create an XMLHTTP instance');
        return false;
    }
    // Here's how to be a bit crafty object.onreadystatechange=Function("yourfunction("+yourargumentlist+");");
    http_request[number].onreadystatechange = Function(actionFunction+"("+number+",'"+entityid+"');");
    http_request[number].open('GET', url, true);
    http_request[number].send(null);
}

function callbackReceiver(number,entityid) {
  if (http_request[number].readyState == 4) {
    if (http_request[number].status == 200) {
      document.getElementById(entityid).innerHTML = http_request[number].responseText;
    }
  }
}

function set_grow_shrink_box(id, display, images, text) {
  if (display == 'none') {
    display = 'inline';
    image = images+'small_shrink.gif'
  } else {
    display = 'none';
    image = images+'small_grow.gif'
  }

  document.getElementById(id).style.display=display;
  str = "<nobr><a onClick=\"set_grow_shrink_box('"+id+"','"+display+"','"+images+"','"+text+"');\">"+text+"<img border=\"0\" src=\""+image+"\"</a></nobr>"
  document.getElementById('button_'+id).innerHTML = str;
}

