<a href="{$url}" class="tab{$active}" style="left:{$x}px;">{$name}</a>

{if $active && $name == "Home" || $current_user->prefs["customizedTheme2"] != 4}
  <style>
  div#main {
    -webkit-border-radius: 0px 12px 12px 12px !important;
       -moz-border-radius: 0px 12px 12px 12px !important;
            border-radius: 0px 12px 12px 12px !important;
  }
  </style>
{/}
