<?php

namespace WBF\components\pluginsframework;

interface TemplatePlugin_Interface {
	public function register_templates( $atts );

	public function view_template( $template );
}