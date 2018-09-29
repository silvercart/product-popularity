<% if $ShowPopularProducts && $isFirstPage %>
    <% with $getPopularProductsForTemplate(10) %>
    <section class="widget mb-3 clearfix">
        <h2 class="d-inline-block">{$Title}</h2>
        <a href="{$PopularProductsLink}" class="d-inline-block ml-2"><span class="fa fa-arrow-right"></span> {$PopularProductsLinkTitle}</a>
        <% include SilverCart\View\GroupView\WidgetProductBoxSlider %>
    </section>
    <% end_with %>
<% end_if %>