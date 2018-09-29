<div class="row row-offcanvas row-offcanvas-right">
<% cached $CacheKey,'popularproducts' %>
    <section id="content-main" class="col-12 <%if $InsertWidgetArea('Sidebar') %>col-md-9<% end_if %>">
        <h2 class="sr-only">{$Title}</h2>
        <% include SilverCart/Model/Pages/BreadCrumbs %>
        <% with $getPopularProductsForTemplate %>
        <article aria-label="{$Title.XML}">
            <header><h1 id="maintitle">{$Title}</h1></header>
            <% include SilverCart\View\GroupView\ProductGroupPageTile %>
        </article>
        <% end_with %>
    </section>
<% end_cached %>
<%if $InsertWidgetArea('Sidebar') %>
    <aside id="sidebar" class="col-12 col-md-3 sidebar-offcanvas">
        {$InsertWidgetArea('Sidebar')}
    </aside>
<% end_if %>
</div>