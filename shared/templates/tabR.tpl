<a href="{$url}" class="tab{$active} noselect" unselectable="on">{$name}</a>

{if $active && $name == "Home"}
  <style>
  div#main {
    -webkit-border-radius: 0px 12px 12px 12px !important;
       -moz-border-radius: 0px 12px 12px 12px !important;
            border-radius: 0px 12px 12px 12px !important;
  }
  </style>
{/}
