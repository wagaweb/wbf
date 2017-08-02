<?php

namespace WBF\components\utils\woocommerce;

use WBF\components\utils\WooCommerce;

trait WBF_Product_Variable_Trait{
	/**
	 * Checks if the product is on sale (for real... :') )
	 *
	 * @return bool
	 */
	public function is_on_sale_for_real(){
		$meta = WooCommerce::get_post_metas($this->id);
		$stock_status = isset($meta['_stock_status']) ? $meta['_stock_status'] : false;
		if(!$stock_status) return false;

		$sale_price = isset($meta['_sale_price'])? $meta['_sale_price'] : 0;
		$min_variation_sale_price = isset($meta['_min_variation_sale_price'])? $meta['_min_variation_sale_price'] : 0;

		if($sale_price > 0 || $min_variation_sale_price > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Get the actual percentage of discount
	 *
	 * @return int
	 */
	public function get_discount_percentage(){
		if(!$this->is_on_sale_for_real()) return 0;

		$max_percentage = 0;
		$variations = $this->get_available_variations();

		for($i = 0; $i < count($variations); ++$i) {
			$variable_product = WooCommerce::get_product($variations[$i]['variation_id']);
			$regular_price = $variable_product->regular_price;
			$sales_price = $variable_product->sale_price;
			$percentage= round((( ( $regular_price - $sales_price ) / $regular_price ) * 100),0) ;
			if($percentage > $max_percentage) {
				$max_percentage = $percentage;
			}
		}

		return $max_percentage;
	}
}