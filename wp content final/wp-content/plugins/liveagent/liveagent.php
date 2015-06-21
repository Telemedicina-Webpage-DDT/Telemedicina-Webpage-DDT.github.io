<?php
/*
 Plugin Name: LiveAgent
Plugin URI: http://www.qualityunit.com/liveagent
Description: Plugin enable integration of Wordpress with LiveAgent
Author: QualityUnit
Version: 4.0.4
Author URI: http://www.qualityunit.com
License: GPL3 - http://gplv3.fsf.org/
*/
if (!defined('LIVEAGENT_PLUGIN_VERSION')) {
    define('LIVEAGENT_PLUGIN_VERSION', '4.0.3');
}
if (!defined('LIVEAGENT_PLUGIN_NAME')) {
    define('LIVEAGENT_PLUGIN_NAME', 'liveagent');
}

include_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . DIRECTORY_SEPARATOR . 'Config.php';
load_plugin_textdomain(LIVEAGENT_PLUGIN_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

$liveagentloadErrorMessage = null;
if (!function_exists('liveagent_PluginLoadError')) {
    function liveagent_PluginLoadError() {
        global $liveagentloadErrorMessage;
        if (current_user_can( 'install_plugins') && current_user_can('manage_options') ) {
            echo '<div class="error"><p>'.$liveagentloadErrorMessage.'</p></div>';
        }
    }
}

try {
    include_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . LIVEAGENT_PLUGIN_NAME . DIRECTORY_SEPARATOR . 'Loader.php';
    $liveagentLoader = liveagent_Loader::getInstance();
    $liveagentLoader->load();
} catch (Exception $e) {
    $liveagentloadErrorMessage = sprintf(__('Critical error during %s plugin load %s', LIVEAGENT_PLUGIN_NAME), LIVEAGENT_PLUGIN_NAME, $e->getMessage());
    add_action( 'admin_notices', 'liveagent_PluginLoadError');
    return;
}

if (!class_exists(LIVEAGENT_PLUGIN_NAME)) {
    class liveagent extends liveagent_Base {

        /**
         * @var liveagent
         */
        private static $instance = null;

        /**
         * @var liveagent_Settings
         */
        private $settings;

        /**
         * @var liveagent_Auth
         */
        private $auth;

        /**
         *  @var liveagent_AjaxHandler
         */
        private $ajaxHandler;

        /**
         * @var boolean
         */
        private $active = false;

        public function activate() {
            if ($this->isActive()) {
                return;
            }
            $this->settings = new liveagent_Settings();
            $this->auth = new liveagent_Auth();
            $this->ajaxHandler = new liveagent_AjaxHandler($this->settings, $this->auth);

            $this->initPlugin();
            $this->active = true;
        }

        public function isActive() {
            return $this->active;
        }

        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * calledby add_filter ('admin_head', array($this, 'initAdminHeader'), 99);
         */
        public function adminInit() {
            $this->settings->initSettingsForAdminPanel();
            add_action('wp_ajax_laping', array($this->ajaxHandler,'liveagentPing'));
        }
        
        private function accountIsValid() {
            return $this->settings->getAccountStatus() != liveagent_Settings::ACCOUNT_STATUS_NOTSET && $this->settings->getAccountStatus() != liveagent_Settings::ACCOUNT_STATUS_CREATING &&
            get_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME) != '' &&
            get_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME) != '' &&
            get_option(liveagent_Settings::LA_URL_SETTING_NAME) != '';
        }
                
        private function initFrontend() {
            if (!$this->accountIsValid()) {
                return;
            }
            add_filter ('wp_footer', array($this, 'initFooter'), 99);
        }

        private function initPlugin() {
            if (!is_admin()) {
                add_action('admin_init', array($this, 'adminInit'));
                $this->initFrontend();
                return;
            }
            add_action('admin_init', array($this, 'adminInit'));
            add_action('admin_menu', array($this, 'addPrimaryConfigMenu'));
            add_filter ('admin_head', array($this, 'initAdminHeader'), 99);
            add_action ('wp_enqueue_scripts', array($this, 'includeJavascripts'));
            add_action ('admin_enqueue_scripts', array($this, 'includeJavascripts'));

            if (!$this->accountIsValid()) {
                return;
            }
            if (is_admin()) {
                try {
                    $this->auth->loginAndGetLoginData();
                } catch (liveagent_Exception_ConnectProblem $e) {
                    return;
                }
            }
        }

        private function importTranslationsToJavascript() {
            $translation_array = array(
                    'installing' => __('Installing', LIVEAGENT_PLUGIN_NAME),
                    'justFewMoreSeconds' => __('Just a few more seconds', LIVEAGENT_PLUGIN_NAME),
                    'completing' => __('Installation completed. Setting up...', LIVEAGENT_PLUGIN_NAME),
                    'youSureResetAccount' => __('Are you sure you want to cancel your account?', LIVEAGENT_PLUGIN_NAME)
            );
            wp_localize_script('liveagent-main', 'liveagentLocalizations', $translation_array );
        }

        private function insertHelpersToJavascript() {
            $helpers_array = array(
                    'afterInstallUrl' => admin_url('admin.php?page=' . liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE),
                    'resetAccountUrl' => admin_url('admin.php?page=' . liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE . '&ac=' . liveagent_Settings::ACTION_RESET_ACCOUNT)
            );
            wp_localize_script('liveagent-main', 'liveagentHelpers', $helpers_array );
        }

        public function includeJavascripts() {
            wp_enqueue_script('liveagent-main', $this->getJsUrl() . 'main.js', array(), LIVEAGENT_PLUGIN_VERSION);
            $this->importTranslationsToJavascript();
            $this->insertHelpersToJavascript();
        }

        public function initAdminHeader($content) {
            if(!is_feed()) {
                echo '<link type="text/css" rel="stylesheet" href="' . $this->getCssUrl() . 'styles.css?ver=' . LIVEAGENT_PLUGIN_VERSION . '" \>' . "\n";
                echo '<link href="http://fonts.googleapis.com/css?family=PT+Sans:regular,italic,bold,italicbold" rel="stylesheet" type="text/css" />' . "\n";
            }
            echo $content;
        }

        public function initFooter() {
            $footer = new liveagent_wordpress_Footer($this->settings);
            $footer->initVisitorFooter();
        }

        public function addPrimaryConfigMenu() {
            $formHandler = new liveagent_Form_Handler($this->settings, $this->auth);
            add_menu_page(__('LiveAgent', LIVEAGENT_PLUGIN_NAME),
                    __('LiveAgent',LIVEAGENT_PLUGIN_NAME),
                    'manage_options',
                    liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE,
                    array($formHandler, 'printPrimaryPage'),
                    $this->getImgUrl() . 'menu-icon.png');
        }
    } //class liveagent extends liveagent_Base {
} //if (!class_exists(LIVEAGENT_PLUGIN_NAME)) {

$liveagent = liveagent::getInstance();
$liveagent->activate();
?>
