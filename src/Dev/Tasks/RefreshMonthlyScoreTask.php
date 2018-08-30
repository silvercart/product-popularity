<?php

namespace SilverCart\ProductPopularity\Dev\Tasks;

use SilverCart\Model\Product\Product;
use SilverCart\ProductPopularity\Model\Product\ProductPopularity;
use SilverStripe\Dev\BuildTask;

/**
 * This task is meant to refresh a products monthly popularity score.
 * Should be used monthly at the beginning of the month.
 * 
 * @package SilverCart
 * @subpackage SubPackage
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 30.08.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class RefreshMonthlyScoreTask extends BuildTask
{
    use \SilverCart\Dev\CLITask;

    /**
     * Set a custom url segment (to follow dev/tasks/)
     *
     * @var string
     */
    private static $segment = 'refresh-monthly-popularity-score';

    /**
     * Shown in the overview on the {@link TaskRunner}.
     * HTML or CLI interface. Should be short and concise, no HTML allowed.
     * 
     * @var string
     */
    protected $title = 'Refresh Monthly Product Popularity Score Task';

    /**
     * Describe the implications the task has, and the changes it makes. Accepts 
     * HTML formatting.
     * 
     * @var string
     */
    protected $description = 'Task to refresh the monthly product popularity score.';
    
    /**
     * Iterates through every product to make sure the monthly popularity score
     * is refreshed.
     * 
     * @param \SilverStripe\Control\HTTPRequest $request Request
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 30.08.2018
     */
    public function run($request)
    {
        $products     = Product::get();
        $currentIndex = 0;
        $total        = $products->count();
        $start        = time();
        foreach ($products as $product) {
            $currentIndex++;
            $this->printProgressPercentageInfoWithTime($currentIndex, $total, time() - $start);
            ProductPopularity::get_current($product);
        }
        $this->printInfo("");
        $this->printInfo("Refreshed the monthly popularity score of {$total} products.");
        $this->printInfo("");
    }
}