<?php

namespace SilverCart\ProductPopularity\Extensions\Pages;

use SilverCart\ProductPopularity\Model\Product\ProductPopularity;
use SilverStripe\Core\Extension;

/**
 * 
 * @package SilverCart
 * @subpackage ProductPopularity_Extensions_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductGroupPageControllerExtension extends Extension
{
    /**
     * List of allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'popularproducts'
    ];

    /**
     * Updates a products popularity score if necessary.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function onBeforeRenderProductDetailView()
    {
        if (!ProductPopularity::can_add_popularity()) {
            return;
        }
        $product = $this->owner->getProduct();
        if (!ProductPopularity::is_first_view($product)) {
            return;
        }
        /* @var $product \SilverCart\Model\Product\Product */
        $product->addPopularity(ProductPopularity::SCORE_VIEW);
        ProductPopularity::mark_as_viewed($product);
    }
}