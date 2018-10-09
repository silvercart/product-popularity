<?php

namespace SilverCart\ProductPopularity\Extensions\Pages;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\ArrayData;

/**
 * Extesnion for SilverCart ProductGroupPage.
 * 
 * @package SilverCart
 * @subpackage ProductPopularity_Extensions_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 29.09.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductGroupPageExtension extends DataExtension
{
    /**
     * Determines whether to show popular products on a product group page or not.
     *
     * @var bool
     */
    private static $show_popular_products = true;
    
    /**
     * Adds a selection of popular products.
     * 
     * @param string &$content Content to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.09.2018
     */
    public function updateBeforeInsertWidgetAreaContent(&$content)
    {
        $content .= $this->owner->renderWith(self::class . "_popularproducts");
    }
    
    /**
     * Adds a navigation item.
     * 
     * @param \SilverStripe\ORM\ArrayList $items Items to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.10.2018
     */
    public function updateDynamicProductGroupNavigationItems($items)
    {
        $ctrl = Controller::curr();
        if ($ctrl->hasAction('popularproducts')) {
            $items->push(ArrayData::create([
                'Link'      => $ctrl->Link('popularproducts'),
                'Title'     => $this->owner->fieldLabel('PopularProducts'),
                'MenuTitle' => $this->owner->fieldLabel('PopularProducts'),
            ]));
        }
    }
    
    /**
     * Updates the bread crumb items.
     * 
     * @param \SilverStripe\ORM\ArrayList $items Bread crumb items
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.09.2018
     */
    public function updateBreadcrumbItems($items)
    {
        $ctrl = Controller::curr();
        if ($ctrl->getAction() === 'popularproducts') {
            $title = DBText::create();
            $title->setValue($this->owner->fieldLabel('PopularProducts'));
            $items->push(ArrayData::create([
                'MenuTitle' => $title,
                'Title'     => $title,
                'Link'      => $ctrl->Link('popularproducts'),
            ]));
        }
    }
    
    /**
     * Updates the field labels.
     * 
     * @param array &$labels Labels to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.09.2018
     */
    public function updateFieldLabels(&$labels)
    {
        $labels = array_merge(
                $labels,
                [
                    'PopularProducts'          => _t(self::class . ".PopularProducts", "Popular Products"),
                    'PopularProductsLinkTitle' => _t(self::class . ".PopularProductsLinkTitle", "Show more popular products"),
                ]
        );
    }
    
    /**
     * Returns all new products of this group and all children.
     * 
     * @param int $limit Optional limit
     * 
     * @return DataList
     */
    public function getPopularProducts($limit = 20)
    {
        $products = $this->owner->getProductsInherited()
                ->sort(['PopularityScoreCurrentMonth'=>'DESC', 'PopularityScoreTotal'=>'DESC']);
        if (!is_null($limit)) {
            $products = $products->limit($limit);
        }
        
        return $products;
        
    }
    
    /**
     * Returns the new products in an ArrayData format to use in a template.
     * 
     * @param int $limit Optional limit
     * 
     * @return ArrayData
     */
    public function getPopularProductsForTemplate($limit = 20)
    {
        return ArrayData::create([
            "Title"                    => _t(self::class . ".PopularProductsIn", "Popular in {productgroup}", ['productgroup' => $this->owner->Title]),
            "Elements"                 => $this->owner->getPopularProducts($limit),
            "ID"                       => "{$this->owner->ID}-popularproducts",
            "PopularProductsLink"      => $this->owner->Link('popularproducts'),
            "PopularProductsLinkTitle" => $this->owner->fieldLabel('PopularProductsLinkTitle'),
        ]);
    }
    
    /**
     * Returns whether to show new products or not.
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.09.2018
     */
    public function ShowPopularProducts()
    {
        $showPopularProducts = $this->owner->config()->get('show_popular_products');
        if ($showPopularProducts
         && !$this->owner->getPopularProducts()->exists()) {
            $showPopularProducts = false;
        }
        return $showPopularProducts;
    }
}