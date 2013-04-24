var selectn_unique_select_id_counter = 1;
;(function($) {
  var methods = {

    // Convert multi-selects into checkboxes
    init : function(options) { 
      this.each(function() {

        // The <select> dropdown
        var select = $(this);
        var select_id = select.attr("id");

        // Give the <select> dropdown an id, if it is missing one
        if (!select_id) {
          select.attr("id","selectn-id-"+selectn_unique_select_id_counter++);
        }

        // First make a label for the closed dropdown list
        var label = $("<span/>",{"class":"selectn-label", "data-select-id":select.attr("id")});
        label.width(select.width());

        // Put the selected options into the label span and add the label's icon
        methods.set_label(label, select);
        select.parent().append(label);

        // Three extra buttons for: all, none, and invert, and a search field
        var op_all = $("<button/>",{"name":"all",   "type":"button","class":"selectn-button"}).text("all").outerHTML();
        var op_non = $("<button/>",{"name":"none",  "type":"button","class":"selectn-button"}).text("none").outerHTML();
        var op_inv = $("<button/>",{"name":"invert","type":"button","class":"selectn-button"}).text("toggle").outerHTML();
        var op_sea = $("<input/>", {"name":"search","type":"text",  "class":"selectn-search"}).outerHTML();

        // Gather up all the options from the <select> dropdown
        var dropdown_ops = [];
        dropdown_ops[dropdown_ops.length] = "<div class='selectn-buttons'>"+op_all+" "+op_non+" "+op_inv+" "+op_sea+"</div>";
        select.find("option").each(function(i, option){ 
          var cb_ops = {"type":"checkbox", "value":$(option).val(), "class":"selectn-cb", "checked":$(option).attr("selected")};
          dropdown_ops[dropdown_ops.length] = "<label class='"+($(option).attr("selected")?"selectn-cb-selected":"")+"'>"+
                                              $("<input/>",cb_ops).outerHTML()+" <span>"+$(option).html().trim()+"</span></label>";
        });
      
        // Create a dropdown box, that has selectable checkboxes in it
        var dropdown = $("<span/>",{ "class": "selectn-dropdown" });
        dropdown.css("min-width",label.width());

        // Create the checkboxes
        dropdown.append(dropdown_ops);
        select.parent().append(dropdown);

        // Open the dropdown when the label is clicked
        label.click(function(){
          var p = $(this).position();
          dropdown.css({"position":"absolute", "left": p.left+"px", "top":p.top+$(this).height()+1+"px" });
          if (dropdown.is(":visible")) {
            methods.close_all();
          } else {
            methods.close_all();
            $(this).addClass("selectn-active");
            dropdown.addClass("selectn-active");
            dropdown.show();
            $(".selectn-search",dropdown).focus();
          }
        });

        // Listen for the all, none, or invert, buttons to be pressed
        $(".selectn-button",dropdown).click(function(){
          if ($(this).attr("name") == "all") {
            $(".selectn-cb:visible",dropdown).each(function(){
              $(this).prop("checked",true);
              $(this).trigger('change');
            });
          } else if ($(this).attr("name") == "none") {
            $(".selectn-cb:visible",dropdown).each(function(){
              $(this).prop("checked",false);
              $(this).trigger('change');
            });
          } else if ($(this).attr("name") == "invert") {
            $(".selectn-cb:visible",dropdown).each(function(){
              $(this).prop("checked",!$(this).is(':checked'));
              $(this).trigger('change');
            });
          }
        });

        // Listen for text typed into the search input
        $(".selectn-search",dropdown).keyup(function(e){
          var needle = $(this).val();
          if (needle.substring(0,1) == "!") { // negate the pattern if leading !
            needle = needle.substring(1, needle.length);
            var negate = 1;
          }
          if (needle == "") {
            $("label",dropdown).show();
          } else {
            $("label",dropdown).each(function(){
              if ($(this).children("span").html().toLowerCase().indexOf(needle.toLowerCase()) >= 0) {
                negate? $(this).hide() : $(this).show();
              } else {
                negate? $(this).show() : $(this).hide();
              }
            });
          }
        });

        // When the checkboxes are clicked, update the original <select> (which is hidden, but still submitted).
        var timeout;
        $(".selectn-cb",dropdown).change(function(e){
          var cb = $(this);
          if (timeout) {
            clearTimeout(timeout);
          }
          timeout = setTimeout(function(){
            var ops = [];
            $(".selectn-cb",dropdown).each(function(){
              if ($(this).is(":checked")) {
                $(this).parent().addClass("selectn-cb-selected");
                ops.push($(this).val());
              } else {
                $(this).parent().removeClass("selectn-cb-selected");
              }
            });
            select.val(ops);
            methods.set_label(label,select);
          },20);
        });

        // Hide the original <select> dropdown.
        select.hide(); 
      });        

      return this;
    },

    // Update the text inside the label when the selected options are changed
    set_label : function (label, select) {
      var label_str = '', comma = '';
      select.find("option").each(function(i, option){ 
        if ($(option).is(":selected")) {
          label_str += comma+$(option).text().trim(); 
          comma = ", ";
        }
      });
      // Put the selected options into the label span and add the label's icon
      label.text(label_str);
      label.append($("<img/>",{"src":"../images/selectn.gif"}));
    },

    // Close all dropdowns
    close_all : function() {
      $(".selectn-label.selectn-active").each(function(){
        var select = $("#"+$(this).attr("data-select-id"));
        var dropdown = $(this).next(".selectn-dropdown.selectn-active");
        $(this).removeClass("selectn-active"); 
        dropdown.removeClass("selectn-active");
        dropdown.hide();
        $("label",dropdown).show(); // restore entries that might have been hidden by a search
        $(".selectn-search",dropdown).val('');
        $(this).trigger("selectn-closed",{"label":$(this), "dropdown":dropdown, "select":select});
      });
    },

    // Nuke the selectors, useful if you want to redraw
    destroy : function(context) { 
      $(".selectn-label",context).remove();
      $(".selectn-dropdown",context).remove();
    }
  };
    
  // jQuery module stuff
  $.fn.selectn = function(method) {
    if (methods[method]) {
      return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
    } else if (typeof method === 'object' || !method) {
      return methods["init"].apply(this,arguments);
    } else {
      $.error('Method '+method+' does not exist on jQuery.selectn');
    }
  }

})(jQuery);


$("html").click(function(e) {
  if (!$(e.target).is('.selectn-dropdown *') && !$(e.target).is('.selectn-label') && !$(e.target).is('.selectn-label *')) {
    $(document).selectn("close_all");
  }
});

$("html").on('keydown', function(e) {
  if (e.keyCode === 27 ) { // ESC
    $(document).selectn("close_all");
  }
});

jQuery.fn.outerHTML = function(s) {
  return s
    ? this.before(s).remove()
    : jQuery("<p>").append(this.eq(0).clone()).html();
};

