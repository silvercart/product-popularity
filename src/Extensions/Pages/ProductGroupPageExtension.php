<?php

namespace SilverCart\ProductPopularity\Extensions\Pages;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\ProductGroupPage;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
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
     * Attributes.
     *
     * @var array
     */
    private static $db = [
        'ShowPopularProducts' => 'Boolean(0)',
    ];

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
    public function updateBeforeInsertWidgetAreaContent(&$content) : void
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
    public function updateDynamicProductGroupNavigationItems($items) : void
    {
        $ctrl = Controller::curr();
        if ($ctrl->hasAction('popularproducts')) {
            $items->push(ArrayData::create([
                'Link'      => $ctrl->Link('popularproducts'),
                'Title'     => _t('SilverCart.Buy2', 'Buy {title1} {title2}', [
                    'title1' => $this->owner->Title,
                    'title2' => $this->owner->fieldLabel('PopularProducts'),
                ]),
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
    public function updateBreadcrumbItems($items) : void
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
     * Updates the CMS fields.
     * 
     * @param FieldList $fields Original fields to update
     * 
     * @return void
     */
    public function updateCMSFields(FieldList $fields) : void
    {
        $showPopularProductField = CheckboxField::create('ShowPopularProducts', $this->owner->fieldLabel('ShowPopularProducts'));
        $fields->insertAfter('ShowNewProducts', $showPopularProductField);
    }
    
    /**
     * Updates the meta title while beeing in the popular products view.
     * 
     * @param string &$metaTitle           Original meta title
     * @param string $plainMetaTitle       Original plain meta title
     * @param string $plainMetaTitleShort  Original plain meta title short
     * @param string $metaTitlePrefix      Meta title prefix
     * @param string $metaTitleSuffix      Meta title suffix
     * @param string $metaTitleShortPrefix Meta title short prefix
     * @param string $metaTitleShortSuffix Meta title short suffix
     * 
     * @return void
     */
    public function updateMetaTitle(string &$metaTitle, string $plainMetaTitle = null, string $plainMetaTitleShort = null, string $metaTitlePrefix = null, string $metaTitleSuffix = null, string $metaTitleShortPrefix = null, string $metaTitleShortSuffix = null) : void
    {
        $ctrl = Controller::curr();
        if ($ctrl->getAction() === 'popularproducts') {
            $metaTitle = _t(ProductGroupPage::class . '.BuyTitle', 'Buy {title}', [
                'title' => "{$this->owner->fieldLabel('Popular')} {$metaTitleShortPrefix}{$plainMetaTitleShort}{$metaTitleShortSuffix}",
            ]);
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
    public function updateFieldLabels(&$labels) : array
    {
        $labels = array_merge(
                $labels,
                [
                    'Popular'                  => _t(self::class . ".Popular", "Popular"),
                    'PopularProducts'          => _t(self::class . ".PopularProducts", "Popular Products"),
                    'PopularProductsLinkTitle' => _t(self::class . ".PopularProductsLinkTitle", "Show more popular products"),
                    'ShowPopularProducts'      => _t(self::class . '.ShowPopularProducts', 'Show popular products'),
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
    public function getPopularProducts(int $limit = 20)
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
    public function getPopularProductsForTemplate($limit = 20) : ArrayData
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
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.09.2018
     */
    public function ShowPopularProducts() : bool
    {
        $showPopularProducts = $this->owner->getShowPopularProducts();
        if ($showPopularProducts
         && !$this->owner->getPopularProducts()->exists()
        ) {
            $showPopularProducts = false;
        }
        return $showPopularProducts;
    }
    
    /**
     * Returns the ShowPopularProducts setting.
     * 
     * @return bool
     */
    public function getShowPopularProducts() : bool
    {
        $showPopularProducts = (bool) $this->owner->getField('ShowPopularProducts');
        if (!$this->owner->getCMSFieldsIsCalled
         && !Tools::isBackendEnvironment()
        ) {
            $this->owner->extend('updateShowPopularProducts', $showPopularProducts);
        }
        return $showPopularProducts;
    }
}