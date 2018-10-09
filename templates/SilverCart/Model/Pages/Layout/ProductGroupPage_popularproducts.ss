<div class="row row-offcanvas row-offcanvas-right">
<% cached $CacheKey,'popularproducts' %>
    <section id="content-main" class="col-12 col-md-9 order-2">
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
    <aside id="sidebar" class="col-12 col-md-3 sidebar-offcanvas order-1 border-right">
        <% include SilverCart\Model\Pages\ProductGroupSidebar %>
        {$InsertWidgetArea('Sidebar')}
    </aside>
</div>