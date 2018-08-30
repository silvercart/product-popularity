<% cached $WidgetCacheKey %>
    <% if $Elements %>
        <% if $FrontTitle %>
<header><h3>{$FrontTitle}</h3></header>
        <% end_if %>
        <% if $UseAsSlider %>
            <% include SilverCart\View\GroupView\WidgetProductBoxSlider %>
        <% else %>
            <% include SilverCart\View\GroupView\WidgetProductBoxTile %>
        <% end_if %>
    <% end_if %>
<% end_cached %>