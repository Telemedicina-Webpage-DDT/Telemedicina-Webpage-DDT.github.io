<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . '/LoadClassException.class.php');

if (!class_exists('liveagent_Loader')) {
    class liveagent_Loader {
        const API_FILE = 'PhpApi.class.php';

        /**
         * @var liveagent_Loader
         */
        private static $instance = null;
        
        /**
         * @return liveagent_Loader
         */
        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new self;
            }
            return self::$instance;
        } 
        
        public function load() {
            $this->loadBaseClasses();
            $this->loadThirdPartyLibraries();
            $this->loadForms();
            $this->loadApi();
            $this->loadHelpers();
            $this->loadWidgets();
        }

        private function loadWidgets() {
        }

        private function loadHelpers() {
            $this->loadClass('liveagent_AjaxHandler');
            $this->loadClass('liveagent_helper_Signup');
            $this->loadClass('liveagent_helper_CompactTracker');
            $this->loadClass('liveagent_wordpress_Footer');
        }

        private function loadForms() {
            $this->loadClass('liveagent_Form_Base');
            $this->loadClass('liveagent_Form_Settings_Account');
            $this->loadClass('liveagent_Form_Settings_ButtonCode');
            $this->loadClass('liveagent_Form_Signup');
            $this->loadClass('liveagent_Form_SignupWait');
            
            $this->loadClass('liveagent_Form_Validator_Base');
            $this->loadClass('liveagent_Form_Validator_Signup');
            $this->loadClass('liveagent_Form_Validator_Account');
        }

        private function loadThirdPartyLibraries() {
            $this->requireClass(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . str_replace('/', DIRECTORY_SEPARATOR, '/lib/forms/class.htmlform.php'));
        }

        private function loadApi() {
            $this->requireClass(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . DIRECTORY_SEPARATOR . self::API_FILE);
        }

        private function loadBaseClasses() {
            $this->loadClass('liveagent_Base');
            $this->loadClass('liveagent_Settings');
            $this->loadClass('liveagent_Auth');
            $this->loadClass('liveagent_Exception_SettingNotValid');
            $this->loadClass('liveagent_Exception_ConnectProblem');
            $this->loadClass('liveagent_Exception_SignupFail');
            $this->loadClass('liveagent_Form_Handler');            
        }

        protected function loadClass($className) {
            if (!class_exists($className, false)) {
                $path = str_replace('_', DIRECTORY_SEPARATOR, $className);
                $path = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $path . '.class.php';
                $this->requireClass($path);
            }
        }

        protected function requireClass($pathToFile) {
            if (!file_exists($pathToFile)) {
                throw new liveagent_LoadClassException('File ' . $pathToFile . ' do NOT exist! Can not continue.');
            }
            require_once $pathToFile;
        }
    }
}
?>