<?php

namespace FisPressShop\includes\wc;

class WCUtilities {

	/**
	 * Wrapper for wc_get_product that caches the results
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	static function wc_get_product($id){
		static $ids;
		if(!isset($ids[$id])){
			$ids[$id] = wc_get_product($id);
		}
		return $ids[$id];
	}

	/**
	 * Output a text input box.
	 *
	 * @param array $field
	 */
	static function woocommerce_wp_text_input( $field ) {
		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
		$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

		switch ( $data_type ) {
			case 'price' :
				$field['class'] .= ' wc_input_price';
				$field['value']  = wc_format_localized_price( $field['value'] );
				break;
			case 'decimal' :
				$field['class'] .= ' wc_input_decimal';
				$field['value']  = wc_format_localized_decimal( $field['value'] );
				break;
			case 'stock' :
				$field['class'] .= ' wc_input_stock';
				$field['value']  = wc_stock_amount( $field['value'] );
				break;
			case 'url' :
				$field['class'] .= ' wc_input_url';
				$field['value']  = esc_url( $field['value'] );
				break;

			default :
				break;
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		echo '<div class="form-field form-field-text ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><p><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /></p>';

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		echo '</div>';
	}

	/**
	 * Output a select input box.
	 *
	 * @param array $field
	 */
	static function woocommerce_wp_select( $field ) {
		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		echo '<div class="form-field form-field-select ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><p><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label></p><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';

		foreach ( $field['options'] as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
		}

		echo '</select> ';

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		echo '</div>';
	}

	/**
	 * Output a multi checkbox input box.
	 *
	 * @param array $field
	 */
	static function woocommerce_wp_multicheckbox( $field ) {
		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['options']       = isset( $field['options'] ) ? $field['options'] : [];
		$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ){
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		if(!is_array($field['value'])){
			$field['value'] = [$field['value']];
		}

		echo '<div class="form-field form-field-checkbox ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><p>'.$field['label'].'</p>';

		if(is_array($field['options'])){
			echo '<div class="options-wrap">';
			foreach($field['options'] as $k => $v){
				$checked = in_array($k,$field['value']) ? "checked='checked'" : "";
				echo '<div class="option-wrap"><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '[]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $k ) . '" ' . $checked . '  ' . implode( ' ', $custom_attributes ) . '/><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $v ) . '</label></div> ';
			}
			echo '</div>';
		}

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}

		echo '</div>';
	}

	/**
	 * Set a product 'outofstock' directly from the DB if needed
	 *
	 * @param $post_id
	 *
	 * @return bool|false|int
	 */
	static function db_maybe_set_out_of_stock($post_id,$pretend = false){
		global $wpdb;
		$posts_table = $wpdb->prefix."posts";
		$metas_table = $wpdb->prefix."postmeta";
		$variations = $wpdb->get_results("SELECT ID FROM {$posts_table} WHERE post_parent = {$post_id}");
		$is_out_of_stock = true;
		foreach($variations as $v){
			$stock_qnt = $wpdb->get_var("SELECT meta_value FROM {$metas_table} WHERE meta_key = '_stock' AND post_id = {$v->ID}");
			$stock_qnt = floatval($stock_qnt);
			if($stock_qnt && $stock_qnt > 0){
				$is_out_of_stock = false;
				break;
			}elseif(is_null($stock_qnt)){
				$is_out_of_stock = false; //I don't know why sometime this is null... so don't know if should or shouldn't do the update.
			}
		}
		if($is_out_of_stock){
			if(!$pretend)
				$q = $wpdb->query("UPDATE {$metas_table} SET meta_value = 'outofstock' WHERE meta_key = '_stock_status' AND post_id = {$post_id}");
			else
				$q = true;
		}else{
			$q = false;
		}

		return $q;
	}

	/**
	 * Get a variation parent directly from the DB
	 *
	 * @param $variation_id
	 *
	 * @return null|string
	 */
	static function db_get_product_variation_parent($variation_id){
		global $wpdb;
		$posts_table = $wpdb->prefix."posts";
		$parent = $wpdb->get_var("SELECT post_parent FROM {$posts_table} WHERE ID = {$variation_id}");
		return $parent;
	}
}