<?php

namespace SilverCart\ProductPopularity\Dev\Tasks;

use SilverCart\Model\Product\Product;
use SilverCart\ProductPopularity\Model\Product\ProductPopularity;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

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
        $this->printInfo("----------------------------------------");
        $this->printInfo("Executing RefreshMonthlyScoreTask.");
        $this->printInfo("");
        $this->deleteEmptyScores();
        $this->printInfo("");
        $this->deleteDeadScores();
        $this->printInfo("");
        $this->refreshMonthlyScore();
        $this->printInfo("");
        $this->printInfo("----------------------------------------");
    }
    
    /**
     * Iterates through every product to make sure the monthly popularity score
     * is refreshed.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    protected function refreshMonthlyScore()
    {
        $this->printInfo("\tRefreshing monthly scores...");
        $this->printInfo("");
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
        $this->printInfo("");
        $this->printInfo("\tRefreshed the monthly popularity score of {$total} products.");
    }
    
    /**
     * Deletes all empty scores which are created before the current month.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    protected function deleteEmptyScores()
    {
        $date = date('Y-m-01');
        $this->printInfo("\tDeleting all empty scores which are created before {$date}...", "31");
        $where       = '"Score" = 0 AND "Created" < \'%s\'';
        $countQuery  = 'SELECT COUNT(ID) AS Total FROM %s WHERE ' . $where;
        $deleteQuery = 'DELETE FROM %s WHERE ' . $where;
        $lastMonth   = date('Y-m');
        if ((int) date('j') === 1) {
            $lastMonth = ((int) date('Y') - 1) . '-12';
        }
        $result = DB::query(sprintf($countQuery,
            ProductPopularity::config()->get('table_name'),
            $lastMonth
        ));
        DB::query(sprintf($deleteQuery,
            ProductPopularity::config()->get('table_name'),
            $lastMonth
        ));
        $total = $result->first()['Total'];
        $this->printInfo("\tDeleted a total of {$total} records.", "31");
    }
    
    /**
     * Deletes all dead scores with broken product relations.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    protected function deleteDeadScores()
    {
        $this->printInfo("\tDeleting all scores with broken product relations...", "31");
        $where       = '"ProductID" NOT IN (SELECT "%s"."ID" FROM %s)';
        $countQuery  = 'SELECT COUNT("%s"."ID") AS Total FROM %s WHERE ' . $where;
        $deleteQuery = 'DELETE FROM %s WHERE ' . $where;
        
        $result = DB::query(sprintf($countQuery,
            ProductPopularity::config()->get('table_name'),
            ProductPopularity::config()->get('table_name'),
            Product::config()->get('table_name'),
            Product::config()->get('table_name')
        ));
        DB::query(sprintf($deleteQuery,
            ProductPopularity::config()->get('table_name'),
            Product::config()->get('table_name'),
            Product::config()->get('table_name')
        ));
        $total = $result->first()['Total'];
        $this->printInfo("\tDeleted a total of {$total} records.", "31");
    }
}