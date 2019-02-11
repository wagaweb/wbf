<?php

namespace WBF\components\mvc;

abstract class WCProductModel extends Model {
	/**
	 * @var \WC_Product
	 */
	private $wc_product;

	/**
	 * Model constructor.
	 *
	 * @param $id
	 * @param bool $load_wp_post
	 * @param bool $load_wc_product
	 * @param bool $bypass_wc_get_product
	 */
	public function __construct($id, $load_wp_post = true, $load_wc_product = true, $bypass_wc_get_product = false) {
		parent::__construct($id,$load_wp_post);
		if($load_wc_product){
			if($bypass_wc_get_product){
				$this->wc_product = $id > 0 ? new \WC_Product($id) : new \WC_Product();
			}else{
				$this->wc_product = $id > 0 ? wc_get_product($id) : new \WC_Product();
			}
		}
	}

	/**
	 * @return \WP_Post|null
	 */
	public function get_wc_product(){
		return $this->wc_product;
	}

	/**
	 * @param \WC_Product $product
	 */
	public function set_wc_product(\WC_Product $product){
		$this->wc_product = $product;
	}

	/**
	 * Refresh own id by post
	 */
	public function refresh_id(){
		if($this->wc_product instanceof \WC_Product){
			$this->set_id($this->wc_product->get_id());
		}
	}
}