<?php

if(class_exists('WBF')) return;

require_once 'PluginCore.php';

/**
 * Backward compatibility class.
 *
 * Class WBF
 */
class WBF extends \WBF\PluginCore{}