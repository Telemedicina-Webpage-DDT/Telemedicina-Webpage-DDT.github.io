<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Form_Settings_ButtonCode extends liveagent_Form_Base {
    private $auth;
    
    /**
     * @var liveagent_Settings
     */
    protected $settings;

    public function __construct(liveagent_Settings $settings, liveagent_Auth $auth) {
        $this->settings = $settings;
        $this->auth = $auth;
        parent::__construct(liveagent_Settings::BUTTONS_SETTINGS_PAGE_NAME, 'options.php');
        if ($this->settings->getButtonCode() == null) {
            $this->setInfoMessages(array(__('The chat button is currently disabled.', LIVEAGENT_PLUGIN_NAME)));
        }
    }

    protected function getTemplateFile() {
        return $this->getTemplatesPath() . 'ButtonCode.xtpl';
    }

    protected function getType() {
        return liveagent_Form_Base::TYPE_FORM;
    }

    protected function getOption($name) {
        if ($name == liveagent_Settings::BUTTON_CODE) {
            return $this->settings->getButtonCode();
        }
        return parent::getOption($name);
    }

    private function getLoginLink() {
        if (is_admin() && current_user_can('manage_options') && current_user_can( 'install_plugins')) {
            try {
                $authToken = $this->settings->getOwnerAuthToken();
                if ($authToken == liveagent_Settings::NO_AUTH_TOKEN) {
                    return $this->settings->getLiveAgentUrl() . '/agent?S='.$this->settings->getOwnerSessionId();
                } else {
                    return $this->settings->getLiveAgentUrl() . '/agent?AuthToken='.$authToken;
                }
            } catch (liveagent_Exception_ConnectProblem $e) {
                return $this->settings->getLiveAgentUrl() . '/agent';
            }
        }
        return $this->settings->getLiveAgentUrl() . '/agent';
    }

    protected function initForm() {
        parent::initForm();

        $this->addTranslation('DoYouNeedHelp', __('Do you need any help with this plugin? Feel free to ', LIVEAGENT_PLUGIN_NAME) . '<a href="http://support.ladesk.com/submit_ticket" target="_blank">'.__('contact us', LIVEAGENT_PLUGIN_NAME).'</a>.');
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChat', __('LiveAgent - Live chat and helpdesk plugin for Wordpress', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('YourAccount',__('Your account', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('url',__('Account url', LIVEAGENT_PLUGIN_NAME));

        $this->addHtml(liveagent_Settings::LA_URL_SETTING_NAME, $this->settings->getLiveAgentUrl());
        $this->addLink('loginLink', __('login', LIVEAGENT_PLUGIN_NAME), $this->getLoginLink());
        $this->addHtml('changeLink', __('change', LIVEAGENT_PLUGIN_NAME));
        $this->addHtml('changeLinkUrl', admin_url('admin.php?page=' . liveagent_Form_Handler::TOP_LEVEL_OPTIONS_HANDLE . '&ac=' . liveagent_Settings::ACTION_CHANGE_ACCOUNT));
        $this->addTextArea(liveagent_Settings::BUTTON_CODE, 50, 10, 'large-text code');
        $this->form->add('html', 'submit', __('Save', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('buttonCode', __('Button code', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('PlaceHereCode', __('Place here the code from your LiveAgent admin panel', LIVEAGENT_PLUGIN_NAME));
    }
}

?>