<?php

namespace WBF\includes;

use WBF\components\compiler\Styles_Compiler;
use WBF\components\customupdater\Plugin_Update_Checker;
use WBF\components\notices\Notice_Manager;
use WBF\components\utils\Utilities;

class ServiceManager{
	/**
	 * @var Notice_Manager
	 */
	private $notice_manager;
	/**
	 * @var \Mobile_Detect
	 */
	private $mobile_detect;
	/**
	 * @var Styles_Compiler
	 */
	private $styles_compiler;
	/**
	 * @var Plugin_Update_Checker
	 */
	private $updater;
	/**
	 * @var GoogleFontsRetriever
	 */
	private $google_fonts_retriever;

	/**
	 * @param Notice_Manager $notice_manager
	 */
	public function set_notice_manager(Notice_Manager $notice_manager){
		$this->notice_manager = $notice_manager;
	}

	/**
	 * @return Notice_Manager
	 */
	public function get_notice_manager(){
		return $this->notice_manager;
	}

	/**
	 * @param \Mobile_Detect $mobile_detect
	 */
	public function set_mobile_detect(\Mobile_Detect $mobile_detect){
		$this->mobile_detect = $mobile_detect;
	}

	/**
	 * @return \Mobile_Detect
	 */
	public function get_mobile_detect(){
		return $this->mobile_detect;
	}

	/**
	 * @param Styles_Compiler $styles_compiler
	 */
	public function set_styles_compiler(Styles_Compiler $styles_compiler){
		$this->styles_compiler = $styles_compiler;
	}

	/**
	 * @return Styles_Compiler
	 */
	public function get_styles_compiler(){
		return $this->styles_compiler;
	}

	/**
	 * @param Plugin_Update_Checker $plugin_update_checker
	 */
	public function set_updater(Plugin_Update_Checker $plugin_update_checker){
		$this->updater = $plugin_update_checker;
	}

	/**
	 * @return Plugin_Update_Checker
	 */
	public function get_updater(){
		return $this->updater;
	}

	/**
	 * @param GoogleFontsRetriever $googleFontsRetriever
	 */
	public function set_google_fonts_retriever(GoogleFontsRetriever $googleFontsRetriever){
		$this->google_fonts_retriever = $googleFontsRetriever;
	}

	/**
	 * @return GoogleFontsRetriever
	 */
	public function get_google_fonts_retriever(){
		return $this->google_fonts_retriever;
	}
}