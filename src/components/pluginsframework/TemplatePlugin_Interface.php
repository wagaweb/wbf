<?php
/*
 * This file is part of WBF Framework: https://www.waboot.io
 *
 * @author WAGA Team
 */

namespace WBF\components\pluginsframework;

interface TemplatePlugin_Interface {
	public function register_templates( $atts );

	public function view_template( $template );
}