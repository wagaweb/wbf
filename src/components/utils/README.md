# Utils
Utils component contains a set of utility classes.

[WooCommerce](https://github.com/wagaweb/wbf/blob/master/src/components/utils/WooCommerce.php) related utilities, especially the [DBUtilities](https://github.com/wagaweb/wbf/blob/master/src/components/utils/woocommerce/DBUtilities.php) , will save you a lot of time we guarantee it!

## Usage of WooCommerce classes

By calling `WBF\components\utils\WooCommerce::replace_wc_product_classes()` during `init` the WooCommerce product classes will be replaced by WBF ones.

These classes provides more functionality and will be further expanded.

You can also get the WBF counterpart of a WC Product class by calling: `WBF\components\utils\WooCommerce::get_wbf_product($product)` where `$product` is a `\WC_Product` or a product id.