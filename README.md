# Product Popularity
Tracks a products popularity by criteria like views, adding to cart, adding to lists or buying a product.

To provide a proper calculation of the monthly popularity score, the task `RefreshMonthlyScoreTask` should be installed.
To run the task, you can either use `sake` (recommended) or the `/dev/tasks` section through the browser.

## Running the task using sake

    ```
    # cd /path/to/your/project/root
    # sake dev/tasks/refresh-monthly-popularity-score
    ```

## Maintainer Contact
* Sebastian Diel <sdiel@pixeltricks.de>

## Requirements
* SilverCart 4.1

## Summary
SilverCart is an Open Source E-Commerce module for the CMS Framework SilverStripe.

For more information about the SilverCart visit http://www.silvercart.org/about/

## License
See LICENSE