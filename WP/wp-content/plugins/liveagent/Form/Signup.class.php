<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Form_Signup extends liveagent_Form_Base {
    private $userName = '';
    
    public function __construct() {
        global $current_user;
        get_currentuserinfo();
        $this->userName = $current_user->display_name;
        if ($this->userName == '') {
            $this->userName = 'name';
        }
        parent::__construct(liveagent_Settings::SIGNUP_SETTINGS_PAGE_NAME, '');  
        if (isset($_POST['la-error-message'])) {
            $this->setErrorMessages(array($_POST['la-error-message']));
        }
    }

    protected function getTemplateFile() {
        return $this->getTemplatesPath() . 'AccountSignup.xtpl';
    }

    protected function getType() {
        return liveagent_Form_Base::TYPE_FORM;
    }

    private function getdomainOnly() {
        return preg_replace('/^(.*\.)?([^.]*\..*)$/', '$2', @$_SERVER['HTTP_HOST']);
    }

    protected function getOption($name) {
        switch ($name) {
            case liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME:
                if (isset($_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME])) {
                    return $_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME];
                }
                return get_bloginfo('admin_email');
            case liveagent_Settings::LA_FULL_NAME:
                if (isset($_POST[liveagent_Settings::LA_FULL_NAME])) {
                    return $_POST[liveagent_Settings::LA_FULL_NAME];
                }
                return $this->userName;
            case liveagent_Settings::LA_URL_SETTING_NAME:
                if (isset($_POST[liveagent_Settings::LA_URL_SETTING_NAME])) {
                    return $_POST[liveagent_Settings::LA_URL_SETTING_NAME];
                }
                $domain = substr($this->getdomainOnly(), 0, strpos($this->getdomainOnly(), '.'));
                $domain = str_replace(array('http://', 'https://'), '', $domain);
                $domain = preg_replace('/[^A-Za-z0-9]/', '', $domain);                
                return $domain;
        }
    }

    protected function initForm() {
        $this->addTranslation('CreateYourFreeAccount', __('Create your Trial account', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('submit', __('Create your account', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChat', __('LiveAgent - Live chat and helpdesk plugin for Wordpress', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChatDescription', __('We want you to enjoy the full functionality of LiveAgent with the trial account.', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('domainSelection', __('Domain selection', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('Email', __('Email', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('fullName', __('Full name', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('yourFirstNameAndLastName', __('Your first name and last name', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('ladeskCom',  __('.ladesk.com', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('iAgreeWith', __('By creating an account I agree to', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('TermsAndConditions', __('Terms & Conditions', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('SkipThisStepIHaveAccount', __('Skip this step, I already have an account', LIVEAGENT_PLUGIN_NAME));
        
        $this->addTextBox(liveagent_Settings::LA_FULL_NAME, null, 'regular-text code');
        $this->addTextBox(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME, null, 'regular-text code');
        $this->addTextBox(liveagent_Settings::LA_URL_SETTING_NAME, null, 'regular-text code');                     
        
        $this->addHtml('skipCreateUrl', admin_url('admin.php?page=' . liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE . '&ac=' . liveagent_Settings::ACTION_SKIP_CREATE));
    }
}

?>