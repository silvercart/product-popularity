<?php

namespace SilverCart\ProductPopularity\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Model\Product\Product;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;

/**
 * Logs the popularity score of a product by month.
 *
 * @package SilverCart
 * @subpackage ProductPopularity_Model_Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductPopularity extends DataObject
{
    const SCORE_VIEW = 1;
    const SCORE_LIST = 3;
    const SCORE_CART = 5;
    const SCORE_BUY  = 10;
    /**
     * Session key
     * 
     * @var string
     */
    const SESSION_KEY = 'SilverCart.ProductPopularity';
    
    /**
     * Session key
     * 
     * @var string
     */
    const VIEWED_SESSION_KEY = self::SESSION_KEY . '.ViewedProductsByID';
    
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'Score'     => DBInt::class,
        'ProductID' => DBInt::class,
    ];
    /**
     * DB table name.
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductPopularity';

    /**
     * Returns the field labels.
     * 
     * @param bool $includerelations Include relations?
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function fieldLabels($includerelations = true)
    {
        return array_merge(
                parent::fieldLabels($includerelations),
                Tools::field_labels_for(self::class),
                [
                    'Product' => _t(self::class . '.Product', 'Product'),
                ]
        );
    }
    
    /**
     * Recalculates the popularity scores after writing.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    protected function onAfterWrite()
    {
        parent::onAfterWrite();
        $product = $this->Product();
        $product->PopularityScoreCurrentMonth = $this->Score;
        $product->PopularityScoreTotal        = self::get_total_score($product);
        $product->write();
    }
    
    /**
     * Returns the related product.
     * 
     * @return Product
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function Product()
    {
        return Product::get()->byID($this->ProductID);
    }
    
    /**
     * Adds the given score.
     * 
     * @param int $score Score to add
     * 
     * @return $this
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function addScore($score)
    {
        $this->Score += $score;
        $this->write();
        return $this;
    }
    
    /**
     * Returns the total popularity score of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return int
     */
    public static function get_total_score(Product $product)
    {
        return (int) self::get()->filter('ProductID', $product->ID)->sum('Score');
    }
    
    /**
     * Returns the popularity score value for the current month of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return int
     */
    public static function get_current_score(Product $product)
    {
        return self::get_current($product)->Score;
    }
    
    /**
     * Returns the popularity score value for the given month of the given product.
     * 
     * @param Product $product Product to get score for
     * @param string  $month   Month [mm] (01,02,03,04,05,06,07,08,09,10,11,12)
     * @param int     $year    Year [yyyy]
     * 
     * @return int
     */
    public static function get_score_by_month(Product $product, $month, $year = null)
    {
        return self::get_by_month($product, $month, $year)->Score;
    }
    
    /**
     * Returns the popularity score for the current month of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return ProductPopularity
     */
    public static function get_current(Product $product)
    {
        return self::get_by_month($product, date('m'));
    }
    
    /**
     * Returns the popularity score for the current month of the given product.
     * 
     * @param Product $product Product to get score for
     * @param string  $month   Month [mm] (01,02,03,04,05,06,07,08,09,10,11,12)
     * @param int     $year    Year [yyyy]
     * 
     * @return ProductPopularity
     */
    public static function get_by_month(Product $product, $month, $year = null)
    {
        if ($product->exists()) {
            if (is_null($year)) {
                $year = date('Y');
            }
            $paddedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
            $popularity  = self::get()->filter('ProductID', $product->ID)->where('"Created" > \'' . $year . '-' . $paddedMonth . '\'')->first();
            if (!($popularity instanceof ProductPopularity)
                || !$popularity->exists()) {
                $popularity = self::create();
                $popularity->ProductID = $product->ID;
                if ($year . '-' . $paddedMonth != date('Y-m')) {
                    $popularity->Created = $year . '-' . $paddedMonth . '-01 00:00:00';
                }
                $popularity->write();
            }
        } else {
            $popularity = self::singleton();
        }
        return $popularity;
    }
    
    /**
     * Returns whether the given product is viewed the first time since the
     * current session was generated.
     * 
     * @param Product $product Product to check
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function is_first_view(Product $product)
    {
        $isFirstView = false;
        $productIDs  = self::get_viewed_product_ids();
        if (!in_array($product->ID, $productIDs)) {
            $isFirstView = true;
        }
        return $isFirstView;
    }
    
    /**
     * Marks the given product as viewed (session based).
     * 
     * @param Product $product Product to check
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function mark_as_viewed(Product $product)
    {
        $productIDs   = self::get_viewed_product_ids();
        if (!in_array($product->ID, $productIDs)) {
            $productIDs[] = $product->ID;
            Tools::Session()->set(self::VIEWED_SESSION_KEY, $productIDs);
            Tools::saveSession();
        }
    }
    
    /**
     * Marks the given product as NOT viewed (session based).
     * 
     * @param Product $product Product to check
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function mark_as_not_viewed(Product $product)
    {
        $productIDs = self::get_viewed_product_ids();
        $key        = array_search($product->ID, $productIDs);
        if (is_int($key)) {
            unset($productIDs[$key]);
            if (empty($productIDs)) {
                $productIDs = null;
            }
            Tools::Session()->set(self::VIEWED_SESSION_KEY, $productIDs);
            Tools::saveSession();
        }
    }
    
    /**
     * Returns the list of viewed products.
     * 
     * @return array
     */
    public static function get_viewed_product_ids()
    {
        $productIDs = Tools::Session()->get(self::VIEWED_SESSION_KEY);
        if (!is_array($productIDs)) {
            $productIDs = [];
        }
        return $productIDs;
    }
}