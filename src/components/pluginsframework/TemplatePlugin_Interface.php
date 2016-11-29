<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\pluginsframework;

interface TemplatePlugin_Interface {
	public function register_templates( $atts );

	public function view_template( $template );
}