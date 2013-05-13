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
        var label = $("<span/>",{"class":"selectn-label", "data-select-id":select.attr("id"), "tabindex":"0"});
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
          var cb_ops = {"type":"checkbox", "value":$(option).val(), "class":"selectn-cb", "checked":$(option).prop("selected")};
          dropdown_ops[dropdown_ops.length] = "<label class='"+($(option).prop("selected")?"selectn-cb-selected":"")+"'>"+
                                              $("<input/>",cb_ops).outerHTML()+" <span>"+$(option).html().trim()+"</span></label>";
        });
      
        // Create a dropdown box, that has selectable checkboxes in it
        var dropdown = $("<span/>",{ "class": "selectn-dropdown" });
        dropdown.css("min-width",label.width());

        // Create the checkboxes
        dropdown.append(dropdown_ops);
        select.parent().append(dropdown);

        // Open the dropdown when the label is clicked
        label.click(function(e) {
          methods.toggle_open(this, dropdown);
        });
        // Open the dropdown when the element has focus and enter is pressed
        label.keypress(function(e) {
          if (e.which == 13) {
            e.preventDefault();
            e.stopPropagation();
            methods.toggle_open(this, dropdown);
          }
        });
        // Move the item focus up and down the options when the arrows are used
        $(".selectn-cb",dropdown).keydown(function(e){
          // Enter pressed, hide the dropdown
          if (e.which == 13) {
            e.preventDefault();
            e.stopPropagation();
            methods.toggle_open(label, dropdown);
          // Down arrow, move focus down an item
          } else if (e.which == 40) {
            e.preventDefault();
            e.stopPropagation();
            var next_label = $(this).parent().nextAll("label:visible").first();
            if (next_label.length) {
              $("label",dropdown).removeClass("hover");
              next_label.addClass("hover");
              $(".selectn-cb",next_label).focus();
            }
          // Up arrow, move focus up an item
          } else if (e.which == 38) {
            e.preventDefault();
            e.stopPropagation();
            var prev_label = $(this).parent().prevAll("label:visible").first();
            if (prev_label.length) {
              $("label",dropdown).removeClass("hover");
              prev_label.addClass("hover");
              $(".selectn-cb",prev_label).focus();

            // If we can't go any further up focus the search box
            } else {
              $("label",dropdown).removeClass("hover");
              $(".selectn-search",dropdown).focus();
            }
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

        // Listen BEFORE text typed into the search input
        $(".selectn-search",dropdown).keydown(function(e){
          // If enter is pressed on the search input, close the dropdown, this makes
          // the dropdown function the same as regular dropdown, which toggle on enter
          if (e.which == 13) {
            e.preventDefault();
            e.stopPropagation();
            methods.toggle_open(label, dropdown);
            return false;

          // Down arrow, move to first checkbox
          } else if (e.which == 40) {
            e.preventDefault();
            e.stopPropagation();
            $(this).blur();
            $(".selectn-cb:visible:first",dropdown).focus().parent().addClass("hover");
          }
        });
 
        // Listen AFTER text typed into the search input
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

    toggle_open : function(label, dropdown){
      var p = $(label).position();
      dropdown.css({"position":"absolute", "left": p.left+"px", "top":p.top+$(label).height()+1+"px" });
      if (dropdown.is(":visible")) {
        methods.close_all();
      } else {
        methods.close_all();
        $(label).addClass("selectn-active");
        dropdown.addClass("selectn-active");
        dropdown.show();
        $(".selectn-search",dropdown).focus();
      }
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

