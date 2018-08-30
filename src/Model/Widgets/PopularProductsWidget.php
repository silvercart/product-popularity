<?php

namespace SilverCart\ProductPopularity\Model\Widgets;

use SilverCart\Model\ {
    Product\Product,
    Translation\TranslationTools,
    Widgets\Widget,
    Widgets\WidgetTools
};
use SilverStripe\Forms\CompositeField;
use SilverStripe\ORM\ {
    ArrayList,
    FieldType\DBBoolean,
    FieldType\DBInt
};

/**
 * Provides the a view of the new products.
 * 
 * You can define the number of products to be shown.
 *
 * @package SilverCart
 * @subpackage ProductPopularity_Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class PopularProductsWidget extends Widget
{
    /**
     * Indicates the number of products that shall be shown with this widget.
     * 
     * @var int
     */
    private static $db = [
        'NumberOfProductsToShow' => DBInt::class,
        'UseAsSlider'            => DBBoolean::class,
    ];
    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    private static $has_many = [
        'PopularProductsWidgetTranslations' => PopularProductsWidgetTranslation::class
    ];
    /**
     * Casted Attributes.
     * 
     * @var array
     */
    private static $casting = [
        'FrontTitle' => 'Text',
    ];
    /**
     * Set default values.
     * 
     * @var array
     */
    private static $defaults = [
        'NumberOfProductsToShow' => 8,
        'UseAsSlider'            => true,
    ];
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartPopularProductsWidget';
    
    /**
     * Getter for the front title depending on the set language
     *
     * @return string
     */
    public function getFrontTitle()
    {
        return $this->getTranslationFieldValue('FrontTitle');
    }
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function fieldLabels($includerelations = true)
    {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),
                [
                    'NumberOfProductsToShow'            => _t(Widget::class . '.NumberOfProductsToShow', 'Number of products to show'),
                    'UseAsSlider'                       => _t(Widget::class . '.UseAsSlider', 'Use as a slider'),
                    'PopularProductsWidgetTranslations' => _t(TranslationTools::class . '.TRANSLATIONS', 'Translations'),
                ]
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Returns a number of topseller products.
     * 
     * @return ArrayList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function Elements()
    {
        if (!$this->NumberOfProductsToShow) {
            $defaults = $this->config()->get('defaults');
            $this->NumberOfProductsToShow = $defaults['NumberOfProductsToShow'];
        }
        
        $products = Product::get()
                ->limit($this->NumberOfProductsToShow)
                ->sort('PopularityScoreCurrentMonth', 'DESC');
        
        return $products;
    }
    
    /**
     * Creates the cache key for this widget.
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.08.2018
     */
    public function WidgetCacheKey()
    {
        $key = WidgetTools::ProductWidgetCacheKey($this);
        return $key;
    }
}