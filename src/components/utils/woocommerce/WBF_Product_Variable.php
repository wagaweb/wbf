<?php

namespace WBF\components\utils\woocommerce;

use WBF\components\utils\WooCommerce;

class WBF_Product_Variable extends \WC_Product_Variable{
	use WBF_Product_Trait,WBF_Product_Variable_Trait{
		WBF_Product_Variable_Trait::is_on_sale_for_real insteadof WBF_Product_Trait;
		WBF_Product_Variable_Trait::get_discount_percentage insteadof WBF_Product_Trait;
	}

	/**
	 * Get only the available children. Without doing much queries as the vanilla wc counterpart
	 *
	 * @return array
	 */
	public function get_available_children(){
		$children = [];
		foreach ( $this->get_children() as $child_id ) {
			$stock_qty = WooCommerce::get_post_meta($child_id,"_stock");
			$stock_status = WooCommerce::get_post_meta($child_id,"_stock_status");
			if(intval($stock_qty) < 0 || $stock_status != "instock"){
				continue;
			}
			$children[] = $child_id;
		}
		return $children;
	}
}