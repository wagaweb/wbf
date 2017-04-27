<?php

namespace WBF\extensions\waboot;

/**
 * Checks whether the theme is Waboot child or Waboot itself.
 *
 * @return bool
 */
function is_waboot(){
	$theme = wp_get_theme();
	$stylesheet = $theme->get_template();
	return $stylesheet == 'waboot';
}