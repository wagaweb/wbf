<?php

namespace WBF\components\utils\woocommerce;

class WBF_Product_Variation extends \WC_Product_Variation{
	use WBF_Product_Trait,WBF_Product_Variation_Trait{
		WBF_Product_Variation_Trait::is_on_sale_for_real insteadof WBF_Product_Trait;
	}
}