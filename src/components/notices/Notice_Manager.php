<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\notices;

class Notice_Manager {

    var $notices = array();

    function __construct(){
        $notices = $this->get_notices();
        if(!empty($notices)){
            foreach($notices as $id => $notice){
                if(isset($notice['condition']) && is_string($notice['condition'])){
                    if($this->conditions_met($notice)){ //Remove notices that mets the conditions
                        unset($notices[$id]);
                        continue;
                    }
                }
            }
            $this->update_notices($notices);
            $this->notices = $notices;
        }
    }

	/**
     * Clears notices. Clears all the notices if no $category is specified.
     *
	 * @param string|null $category
	 */
    function clear_notices($category = null){
        $notices = $this->get_notices();
	    if(!is_array($notices)){
		    $notices = []; //Correct data if broken
	    }
        if(isset($category)){
            foreach($notices as $k => $notice){
                if($notice['category'] == $category){
                    unset($notices[$k]);
                }
            }
        }else{
            $notices = array();
        }

        $this->notices = $notices;
        $this->update_notices($notices);
    }

    /**
     * Enqueue the notices. WBF Plugin will hook this function to "init" callback, after "wbf_init".
     */
    function enqueue_notices(){
        add_action( 'admin_notices', array($this,'show_automatic_notices'));
    }

    /**
     * Show the notices
     */
    public function show_notices($type = "automatic"){
         foreach($this->notices as $id => $notice){
            if($notice['manual_display'] && $type == "automatic") continue;
            if(!$notice['manual_display'] && $type == "manual") continue;

            /*
             * LOL! We need "inline" class for manual notices because WordPress automatically append notices without this class after the first .wrap h1 or the first .wrap h2, see: wp-admin/js/common.js:400
             */

            switch($notice['level']){
                case 'updated':
                case 'success':
                    ?>
                    <div class="updated <?php if($notice['manual_display']): ?>inline<?php endif; ?>">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
                case 'error':
                    ?>
                    <div class="error <?php if($notice['manual_display']): ?>inline<?php endif; ?>">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
                case 'nag':
                case 'warning':
                    ?>
                    <div class="update-nag <?php if($notice['manual_display']): ?>inline<?php endif; ?>">
                        <p><?php echo $notice['message']; ?></p>
                    </div>
                    <?php
                    break;
            }
	        if($notice['category'] == "_flash_"){
		        $this->remove_notice($id);
	        }
        }
    }

	/**
	 * Print out automatic notices
	 */
    function show_automatic_notices(){
        $this->show_notices("automatic");
    }

	/**
	 * Print out manual notices
	 */
    function show_manual_notices(){
        $this->show_notices("manual");
    }

	/**
     * Get all notices
     *
	 * @return array|FALSE
	 */
    private function get_notices(){
        $notices = get_option("wbf_admin_notices",array());
        return $notices;
    }

	/**
	 * Add a new notice to the system
	 *
	 * @param string $id an unique identifier
	 * @param string $message the notice content
	 * @param string $level (can be: "updated","error","nag"
	 * @param string $category (can be anything. Categories are used to group notices for easy clearing them later. If the category is set to "_flash_", however, the notice will be cleared after displaying.
	 * @param null|String $condition a class name that implements Condition interface
	 * @param null|mixed $cond_args parameters to pass to $condition constructor
	 * @param bool $manual_display if TRUE, the notice will not be displayed at "admin_notices" hook.
	 */
	function add_notice($id,$message,$level,$category = 'base', $condition = null, $cond_args = null, $manual_display = false){
        $notices = $this->get_notices();
        $notices[$id] = array(
            'message' => $message,
            'level'   => $level,
            'category' => $category,
            'condition' => $condition,
            'condition_args' => $cond_args,
            'manual_display' => $manual_display,
        );
        $this->notices = $notices;
        $this->update_notices($notices);
    }

	/**
     * Remove a notice
     *
	 * @param string $id
	 */
    function remove_notice($id){
        $notices = $this->get_notices();
        if(isset($notices[$id])) unset($notices[$id]);
        $this->notices = $notices;
        $this->update_notices($notices);
    }

	/**
     * Update notices option
     *
	 * @param $notices
	 *
	 * @return bool
	 */
    function update_notices($notices){
	    $current_notices = get_option("wbf_admin_notices",array());
	    if(is_array($notices)){
		    $result = update_option("wbf_admin_notices", $notices);
	    }else{
		    $result = update_option("wbf_admin_notices", $current_notices);
	    }
        return $result;
    }

	/**
     * Check if conditions are met
     *
	 * @param $notice
	 *
	 * @return bool
	 * @throws \Exception
	 */
    private function conditions_met($notice){
        $className = $notice['condition'];
        if(!class_exists($className)) throw new \Exception("The condition class ({$className}) for the notice does not exists");
        if(isset($notice['condition_args'])){
            $cond = new $className($notice['condition_args']);
        }else{
            $cond = new $className();
        }
        if($cond){
            if($cond->verify()){
                return true;
            }
        }
        return false;
    }
}