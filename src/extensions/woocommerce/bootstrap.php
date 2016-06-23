<?php

namespace WBF\extensions\woocommerce;

add_filter("wbf/utilities/get_filtered_post_types/blacklist",'\WBF\extensions\woocommerce\add_wc_post_types_to_invalid_for_behaviors');
function add_wc_post_types_to_invalid_for_behaviors($post_types){
	$post_types[] = "webhooks";
	$post_types[] = "shop_webhook";
	$post_types[] = "shop_coupon";
	$post_types[] = "product_variation";
	$post_types[] = "shop_order";
	$post_types[] = "shop_order_refund";
	return $post_types;
}