<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Form_Settings_Account extends liveagent_Form_Base {
    /**
     * @var liveagent_Auth
     */
    private $auth;
    
    /**
     * @var liveagent_Settings
     */
    protected $settings;

    public function __construct(liveagent_Settings $settings, liveagent_Auth $auth) {
        $this->settings = $settings;
        $this->auth = $auth;

        parent::__construct(liveagent_Settings::GENERAL_SETTINGS_PAGE_NAME, '');
    }

    protected function getTemplateFile() {
        return $this->getTemplatesPath() . 'AccountSettings.xtpl';
    }
    
    protected function getUnderErrorMessagesText() {
        if (get_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME) == null ||
        get_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME) == null ||
        get_option(liveagent_Settings::LA_URL_SETTING_NAME) == null) {
            return '';
        }
        try {
            $this->auth->ping(null, true);
            $this->auth->loginAndGetLoginData();
        } catch (liveagent_Exception_ConnectProblem $e) {
            return '';
        }
            
        
        return '<p>' . __('Okay, I want to', LIVEAGENT_PLUGIN_VERSION) . '&nbsp;<a href="'.admin_url('admin.php?page=' . liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE).'">'.__('cancel this change', LIVEAGENT_PLUGIN_NAME).'</a>.</p>';
    }

    protected function getType() {
        return liveagent_Form_Base::TYPE_FORM;
    }
    
    protected function getOption($name) {
        if ($name == liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME && isset($_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME])) {
            return $_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME];
        }
        if ($name == liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME && isset($_POST[liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME])) {
            return $_POST[liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME];
        }
        if ($name == liveagent_Settings::LA_URL_SETTING_NAME && isset($_POST[liveagent_Settings::LA_URL_SETTING_NAME])) {
            return $_POST[liveagent_Settings::LA_URL_SETTING_NAME];
        }
        return parent::getOption($name);
    }

    protected function initForm() {
        parent::initForm();
        $this->addTranslation('url', __('Url', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('username', __('Username', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('password', __('Password', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('DoYouNeedHelp', __('Do you need any help with this plugin? Feel free to ', LIVEAGENT_PLUGIN_NAME) . '<a href="http://support.ladesk.com/submit_ticket" target="_blank">'.__('contact us', LIVEAGENT_PLUGIN_NAME).'</a>.');
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChat', __('LiveAgent - Live chat and helpdesk plugin for Wordpress', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('AccountSettings', __('Account settings', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('urlWrereLaIsLcated', __('Url where your LiveAgent installation is located', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('usernameWhichYouUsingToLogin', __('Username which you use to login to your Live Agnet', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('YourPassword', __('Your password', LIVEAGENT_PLUGIN_NAME));
        $this->addTextBox(liveagent_Settings::LA_URL_SETTING_NAME, null, 'regular-text code');
        $this->addTextBox(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME, null, 'regular-text code');
        $this->addPassword(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME, null, 'regular-text code');
        $this->addHtml('submit', __('Save Account Settings', LIVEAGENT_PLUGIN_NAME));
        $this->addHtml('resetAccount', __('Reset everything', LIVEAGENT_PLUGIN_NAME));
        $this->addHtml('resetDescription', __('this will clear all your existing account settings and offer you to create a new trial account',LIVEAGENT_PLUGIN_NAME));
    }
}
