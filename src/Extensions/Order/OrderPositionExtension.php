<?php

namespace SilverCart\ProductPopularity\Extensions\Order;

use SilverCart\Model\Product\Product;
use SilverCart\ProductPopularity\Model\Product\ProductPopularity;
use SilverStripe\ORM\DataExtension;

/**
 * Order extension to add the popularity feature.
 *
 * @package SilverCart
 * @subpackage ProductPopularity_Extensions_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class OrderPositionExtension extends DataExtension
{
    /**
     * 
     * Increases the popularity score after buying a product.
     * 
     * @param \SilverCart\Model\Order\ShoppingCartPosition $shoppingCartPosition Shopping cart position
     * @param \SilverCart\Model\Order\OrderPosition        $orderPosition        Order position
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (!ProductPopularity::can_add_popularity()) {
            return;
        }
        $isNewRecord = $this->owner->isChanged('ID');
        if ($isNewRecord) {
            $product = $this->owner->Product();
            if ($product instanceof Product) {
                $product->addPopularity(ProductPopularity::SCORE_BUY);
            }
        }
    }
}