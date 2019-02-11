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
	 * @param bool $instantiate_directly
	 *
	 * @throws ModelException
	 */
	public function __construct($id, $load_wp_post = true, $load_wc_product = true, $instantiate_directly = false) {
		parent::__construct($id,$load_wp_post);
		if($load_wc_product){
			$this->load_wc_product($instantiate_directly);
		}
	}

	/**
	 * @return \WC_Product|null
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
	 * @param bool $instantiate_directly
	 */
	public function load_wc_product($instantiate_directly = false){
		if($instantiate_directly) {
			$this->wc_product = $this->get_id() > 0 ? new \WC_Product($this->get_id()) : new \WC_Product();
		}else{
			$this->wc_product = $this->get_id() > 0 ? wc_get_product($this->get_id()) : new \WC_Product();
		}
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