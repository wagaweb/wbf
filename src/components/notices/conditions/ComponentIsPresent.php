<?php

namespace WBF\components\notices\conditions;

use WBF\modules\components\ComponentsManager;

class ComponentIsPresent implements Condition {

    var $c_name;

    function __construct($c_name){
        $this->c_name = $c_name;
    }

    function verify(){
        $registered_components = ComponentsManager::getAllComponents();
        if(isset($registered_components[$this->c_name]) && is_file($registered_components[$this->c_name]['file'])){
            return true;
        }

        return false;
    }
} 