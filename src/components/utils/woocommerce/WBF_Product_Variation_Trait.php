<?php

namespace WBF\components\utils\woocommerce;

use WBF\components\utils\WooCommerce;

trait WBF_Product_Variation_Trait{
	/**
	 * Checks if the product is on sale (for real... :') )
	 *
	 * @return bool
	 */
	public function is_on_sale_for_real(){
		$meta = WooCommerce::get_post_metas($this->variation_id);
		$stock_status = isset($meta['_stock_status']) ? $meta['_stock_status'] : false;
		if(!$stock_status) return false;

		$sale_price = isset($meta['_sale_price'])? $meta['_sale_price'] : 0;

		if($sale_price > 0){
			return true;
		}else{
			return false;
		}
	}
}