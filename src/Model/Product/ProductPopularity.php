<?php

namespace SilverCart\ProductPopularity\Model\Product;

use SilverCart\Dev\Tools;
use SilverCart\Model\Customer\Customer;
use SilverCart\Model\Product\Product;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
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
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function singular_name() :string
    {
        return Tools::singular_name_for($this);
    }

    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string
     */
    public function plural_name() : string
    {
        return Tools::plural_name_for($this); 
    }

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
    public function fieldLabels($includerelations = true) : array
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
     * @since 23.01.2019
     */
    protected function onAfterWrite() : void
    {
        parent::onAfterWrite();
        if ($this->isCurrentScore()) {
            $currentScore = (int) $this->Score;
            $totalScore   = self::get_total_score($this->Product());
            $tableName    = Product::config()->get('table_name');
            DB::query("UPDATE {$tableName} SET PopularityScoreCurrentMonth = {$currentScore}, PopularityScoreTotal = {$totalScore} WHERE ID = {$this->ProductID}");
        }
    }
    
    /**
     * Returns the related product.
     * 
     * @return Product
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function Product() : ?Product
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
     * @since 04.09.2018
     */
    public function addScore($score) : ProductPopularity
    {
        $product = $this->Product();
        if ($product instanceof Product
         && $product->exists()
        ) {
            $this->Score += $score;
            $this->write();
        }
        return $this;
    }
    
    /**
     * Returns whether this ProductPopularity score is the current months score.
     * 
     * @return bool
     */
    public function isCurrentScore() : bool
    {
        return substr($this->Created, 0, 7) == date('Y-m');
    }
    
    /**
     * Returns the total popularity score of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return int
     */
    public static function get_total_score(?Product $product) : int
    {
        $score = 0;
        if ($product instanceof Product) {
            $score = (int) self::get()->filter('ProductID', $product->ID)->sum('Score');
        }
        return $score;
    }
    
    /**
     * Returns the popularity score value for the current month of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return int
     */
    public static function get_current_score(Product $product) : int
    {
        return (int) self::get_current($product)->Score;
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
    public static function get_score_by_month(Product $product, $month, $year = null) : int
    {
        return (int) self::get_by_month($product, $month, $year)->Score;
    }
    
    /**
     * Returns the popularity score for the current month of the given product.
     * 
     * @param Product $product Product to get score for
     * 
     * @return ProductPopularity
     */
    public static function get_current(Product $product) : ProductPopularity
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
    public static function get_by_month(Product $product, $month, $year = null) : ProductPopularity
    {
        if ($product->exists()) {
            if (is_null($year)) {
                $year = date('Y');
            }
            $paddedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
            $lastDay     = date('t', strtotime("{$year}-{$paddedMonth}-01"));
            $popularity  = self::get()->filter('ProductID', $product->ID)->where("Created >= '{$year}-{$paddedMonth}-01 00:00:00' && Created <= '{$year}-{$paddedMonth}-{$lastDay} 23:59:59'")->first();
            if (!($popularity instanceof ProductPopularity)
                || !$popularity->exists()) {
                $popularity = self::create();
                $popularity->ProductID = $product->ID;
                if ("{$year}-{$paddedMonth}" != date('Y-m')) {
                    $popularity->Created = "{$year}-{$paddedMonth}-01 00:00:00";
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
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function is_first_view(Product $product) : bool
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
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function mark_as_viewed(Product $product) : void
    {
        $productIDs = self::get_viewed_product_ids();
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
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function mark_as_not_viewed(Product $product) : void
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
    public static function get_viewed_product_ids() : array
    {
        $productIDs = Tools::Session()->get(self::VIEWED_SESSION_KEY);
        if (!is_array($productIDs)) {
            $productIDs = [];
        }
        return $productIDs;
    }
    
    /**
     * Returns false if the current user is an admin and the current environment
     * mode is live.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public static function can_add_popularity() : bool
    {
        return !(Customer::is_admin() && Director::isLive());
    }
}