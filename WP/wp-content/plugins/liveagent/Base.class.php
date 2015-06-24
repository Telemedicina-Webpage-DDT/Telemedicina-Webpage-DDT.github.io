<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

if (!class_exists('liveagent_Base')) {
    class liveagent_Base {
        const IMG_PATH = 'img/';
        const TEMPLATES_PATH = 'templates/';
        const JS_PATH = 'js/';
        const CSS_PATH = 'css/';
        
        const REMOTE_SCRIPTS_DIR = 'scripts/';
         
        protected function _log($message) {
            if( is_array( $message ) || is_object( $message ) ){
                $message = var_export($message, true);
            }
            $message = LIVEAGENT_PLUGIN_NAME . ' plugin log: ' . $message;
            error_log($message);
            if ($this->isPluginDebugMode()) {
                echo $message;
            }
        }

        protected function isPluginDebugMode() {
            return defined('LIVEAGENT_DEBUG_MODE') && LIVEAGENT_DEBUG_MODE == true;
        }

        protected function getTemplatesPath() {
            return WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . DIRECTORY_SEPARATOR . self::TEMPLATES_PATH;
        }

        protected function getImgUrl() {
            return WP_PLUGIN_URL . '/' . LIVEAGENT_PLUGIN_NAME . '/' . self::IMG_PATH;
        }

        protected function getJsUrl() {
            return WP_PLUGIN_URL . '/' . LIVEAGENT_PLUGIN_NAME . '/' . self::JS_PATH;
        }

        protected function getCssUrl() {
            return WP_PLUGIN_URL . '/' . LIVEAGENT_PLUGIN_NAME . '/' . self::CSS_PATH;
        }

        public function getRemoteTrackJsUrl() {
            return get_option(liveagent_Settings::LA_URL_SETTING_NAME) . '/'.self::REMOTE_SCRIPTS_DIR.'trackjs.php';
        }

        public function getRemotePixUrl() {
            return get_option(liveagent_Settings::LA_URL_SETTING_NAME) . '/'.self::REMOTE_SCRIPTS_DIR.'pix.gif';
        }

        public function getRemoteApiUrl($url = null) {
            if ($url === null) {
                return get_option(liveagent_Settings::LA_URL_SETTING_NAME) . '/api/index.php';
            }
            return $url . '/api/index.php';
        }
    }
}

?>