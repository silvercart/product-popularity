<?php

namespace SilverCart\ProductPopularity\Extensions\Product;

use SilverCart\Dev\Tools;
use SilverCart\ProductPopularity\Model\Product\ProductPopularity;
use SilverStripe\ORM\DataExtension;

/**
 * Product extension to add the popularity feature.
 *
 * @package SilverCart
 * @subpackage ProductPopularity_Extensions_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductExtension extends DataExtension
{
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'PopularityScoreTotal'        => 'Int',
        'PopularityScoreCurrentMonth' => 'Int',
    ];
    
    /**
     * Updates the CMS fields.
     * 
     * @param \SilverStripe\Forms\FieldList $fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function updateCMSFields(\SilverStripe\Forms\FieldList $fields)
    {
        $fields->removeByName('PopularityScoreTotal');
        $fields->removeByName('PopularityScoreCurrentMonth');
    }
    
    /**
     * Updates the field labels.
     * 
     * @param array &$labels Labels to update
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function updateFieldLabels(&$labels)
    {
        $labels = array_merge(
                $labels,
                Tools::field_labels_for(self::class)
        );
    }
    
    /**
     * Increases the popularity score after adding a product to cart.
     * 
     * @param \SilverCart\Model\Order\ShoppingCartPosition $position
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function onAfterAddToCart($position, $isNewPosition)
    {
        if ($isNewPosition) {
            $this->owner->addPopularity(ProductPopularity::SCORE_CART);
        }
    }
    
    /**
     * Increases the popularity score after adding a product to a list.
     * 
     * @param \SilverCart\ProductLists\Model\Product\ProductListPosition $position
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function onAfterAddToList($position)
    {
        $this->owner->addPopularity(ProductPopularity::SCORE_LIST);
    }
    
    /**
     * Returns all related popularity object.
     * Each popularity represents the score of one month.
     * 
     * @return \SilverCart\ORM\DataList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function ProductPopularities()
    {
        return ProductPopularity::get()->filter('ProductID', $this->owner->ID);
    }
    
    /**
     * Alias for $this->ProductPopularities().
     * 
     * @return \SilverCart\ORM\DataList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function Popularities()
    {
        return $this->owner->ProductPopularities();
    }
    
    /**
     * Returns the current popularity object.
     * 
     * @return ProductPopularity
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function CurrentPopularity()
    {
        return ProductPopularity::get_current($this->owner);
    }
    
    /**
     * Returns the current popularity score.
     * 
     * @return int
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function CurrentPopularityScore()
    {
        return ProductPopularity::get_current_score($this->owner);
    }
    
    /**
     * Returns the current popularity score.
     * 
     * @return int
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function TotalPopularityScore()
    {
        return ProductPopularity::get_total_score($this->owner);
    }
    
    /**
     * Adds the given score to the products popularity.
     * 
     * @return \SilverCart\Model\Product\Product
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function addPopularity($score)
    {
        $this->owner->CurrentPopularity()->addScore($score);
        return $this->owner;
    }
}