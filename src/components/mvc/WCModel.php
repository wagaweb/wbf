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
	 * @param bool $loadWCProduct
	 * @param bool $instantiateDirectly
	 *
	 * @throws ModelException
	 */
	public function __construct($id, $load_wp_post = true, $loadWCProduct = true, $instantiateDirectly = false)
	{
		parent::__construct($id,$load_wp_post);
		if($loadWCProduct){
			$this->loadWCProduct($instantiateDirectly);
		}
	}

	/**
	 * @return \WC_Product|null
	 */
	public function getWCProduct()
	{
		return $this->wc_product;
	}

	/**
	 * @param \WC_Product $product
	 */
	public function setWCProduct(\WC_Product $product)
	{
		$this->wc_product = $product;
	}

	/**
	 * @param bool $instantiate_directly
	 */
	public function loadWCProduct($instantiate_directly = false)
	{
		if($instantiate_directly) {
			$this->wc_product = $this->getId() > 0 ? new \WC_Product($this->getId()) : new \WC_Product();
		}else{
			$this->wc_product = $this->getId() > 0 ? wc_get_product($this->getId()) : new \WC_Product();
		}
	}

	/**
	 * Refresh own id by post
	 */
	public function refreshId()
	{
		if($this->wc_product instanceof \WC_Product){
			$this->setId($this->wc_product->get_id());
		}
	}
}