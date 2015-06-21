<?php
/**
 *   @copyright Copyright (c) 2012 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Form_Handler extends liveagent_Base {

    const TOP_LEVEL_OPTIONS_HANDLE = 'la-top-level-options-handle';

    private $settings;
    private $auth;

    public function __construct(liveagent_Settings $settings, liveagent_Auth $auth) {
        $this->auth = $auth;
        $this->settings = $settings;
    }
    
    private function canLogIn($url, $usernam, $password) {
        try {
            $this->auth->tryToLogin($url, $usernam, $password);
            return true;
        } catch (liveagent_Exception_ConnectProblem $e) {            
            return false;
        }
    }
    
    private function canPing($url) {
        try {
            $this->auth->ping($url, true);
            return true;
        } catch (liveagent_Exception_ConnectProblem $e) {                    
            return false;
        }
    }
    
    private function resetAccountSettings() {
        update_option(liveagent_Settings::ACCOUNT_STATUS, liveagent_Settings::ACCOUNT_STATUS_NOTSET);
        update_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME, null);
        update_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME, null);
        update_option(liveagent_Settings::LA_URL_SETTING_NAME, null);
        update_option(liveagent_Settings::BUTTON_CODE,null);
        update_option(liveagent_Settings::OWNER_AUTHTOKEN, null);
        update_option(liveagent_Settings::OWNER_SESSIONID, null);
    }
    
    private function saveAccountSettings() {
        try {
            $this->settings->getOwnerAuthToken();
        }catch (liveagent_Exception_ConnectProblem $e) {            
        }
        update_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME, $_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME]);
        update_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME, $_POST[liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME]);
        update_option(liveagent_Settings::LA_URL_SETTING_NAME, $_POST[liveagent_Settings::LA_URL_SETTING_NAME]);
        $this->settings->setButtonCode(
                $this->settings->getIntegrationCode($this->settings->getLiveAgentUrl(), liveagent_Settings::DEFAULT_BUTTON_CODE)
        );
    }

    public function printPrimaryPage() {
        if ($this->settings->getAccountStatus() == liveagent_Settings::ACCOUNT_STATUS_SET) {
            if (!isset($_POST['submit']) && isset($_GET['ac']) && $_GET['ac'] == liveagent_Settings::ACTION_CHANGE_ACCOUNT) {
                $form = new liveagent_Form_Settings_Account($this->settings, $this->auth);
                $form->render();
                return;
            }
            if (!isset($_POST['submit']) && isset($_GET['ac']) && $_GET['ac'] == liveagent_Settings::ACTION_RESET_ACCOUNT) {
                $this->resetAccountSettings();
                $form = new liveagent_Form_Signup();
                $form->render();
                return;
            }
            if (isset($_POST['submit']) && isset($_POST['option_page']) && $_POST['option_page']==liveagent_Settings::GENERAL_SETTINGS_PAGE_NAME) {
                $validator = new liveagent_Form_Validator_Account();
                $validator->setFields($_POST);
                $form = new liveagent_Form_Settings_Account($this->settings, $this->auth);
                if (!$validator->isValid()) {
                    $form->setErrorMessages($validator->getErrors());
                    $form->render();
                    return;
                }
                if (!$this->canPing($_POST[liveagent_Settings::LA_URL_SETTING_NAME])) {                
                    $form->setErrorMessages(array(__('Unable to connect to', LIVEAGENT_PLUGIN_NAME) . ' ' . $_POST[liveagent_Settings::LA_URL_SETTING_NAME]));
                    $form->render();
                    return;
                }
                if (!$this->canLogIn($_POST[liveagent_Settings::LA_URL_SETTING_NAME], $_POST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME], $_POST[liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME])) {
                    $form->setErrorMessages(array(__('Unable to connect - wrong name or password', LIVEAGENT_PLUGIN_NAME)));
                    $form->render();
                    return;
                }
                $this->saveAccountSettings();
                $form = new liveagent_Form_Settings_ButtonCode($this->settings, $this->auth);
                $form->render();
                return;                
            }
            if (get_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME)==null || get_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME)==null || get_option(liveagent_Settings::LA_URL_SETTING_NAME)==null) {
                $form = new liveagent_Form_Settings_Account($this->settings, $this->auth);
                $form->render();
                return;
            }
            $form = new liveagent_Form_Settings_Account($this->settings, $this->auth);
            if (!$this->canPing(get_option(liveagent_Settings::LA_URL_SETTING_NAME))) {
                $form->setErrorMessages(array(__('Unable to connect', LIVEAGENT_PLUGIN_NAME)));
                $form->render();
                return;
            }
            if (!$this->canLogIn(get_option(liveagent_Settings::LA_URL_SETTING_NAME), get_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME), get_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME))) {
                $form->setErrorMessages(array(__('Wrong username or password', LIVEAGENT_PLUGIN_NAME)));
                $form->render();
                return;
            }
            $form = new liveagent_Form_Settings_ButtonCode($this->settings, $this->auth);
            $form->render();
            return;
        }
        //
        if ($this->settings->getAccountStatus() == liveagent_Settings::ACCOUNT_STATUS_CREATING) {
            $this->handleAccountInstallation();
            return;
        }
        //
        if ($this->settings->getAccountStatus() == liveagent_Settings::ACCOUNT_STATUS_NOTSET || $this->settings->getAccountStatus() == null) {
            $this->handleAccountSignup();
        }
    }
    
    private function handleAccountInstallation() {
        if (get_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT) == null) {
            update_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT, 0);
        } else {
            $count = get_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT);
            $count ++;
            update_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT, $count);
        }
        if (get_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT) > 2) {
            update_option(liveagent_Settings::ACCOUNT_STATUS, liveagent_Settings::ACCOUNT_STATUS_SET);
            update_option(liveagent_Settings::ACCOUNT_CREATING_DIALOG_LOAD_COUNT, 0);
        }
        $form = new liveagent_Form_SignupWait($this->settings);
        $form->render();
    }

    private function handleAccountSignup() {
        if (isset($_POST['submit'])) {
            $validator = new liveagent_Form_Validator_Signup();
            $validator->setFields($_POST);
            $form = new liveagent_Form_Signup();
            if (!$validator->isValid()) {
                $form->setErrorMessages($validator->getErrors());
                $form->render();
                return;
            }
            try {
                $this->trySignup();
            } catch (liveagent_Exception_SignupFail $e) {
                $form->setErrorMessages(array($e->getMessage()));
                $form->render();
                return;
            }
            update_option(liveagent_Settings::ACCOUNT_STATUS, liveagent_Settings::ACCOUNT_STATUS_CREATING);
            $form = new liveagent_Form_SignupWait($this->settings);
            $form->render();
            return;
        }
        if (isset($_REQUEST['ac']) && $_REQUEST['ac'] == liveagent_Settings::ACTION_SKIP_CREATE) {
            update_option(liveagent_Settings::ACCOUNT_STATUS, liveagent_Settings::ACCOUNT_STATUS_SET);
            $form = new liveagent_Form_Settings_Account($this->settings, $this->auth);
            $form->render();
            return;
        }
        $form = new liveagent_Form_Signup();
        $form->render();
    }

    private function sendSignupRequest($name, $email, $domain, $password, $papvisitorId) {
        $signupHelper = new liveagent_helper_Signup();
        try {
            $response = $signupHelper->signup(esc_attr($name), $email, $domain, $password, $papvisitorId);
        } catch (La_Exception $e) {
            $errorMessage = __('Signup failed. Please try again in few minutes.', LIVEAGENT_PLUGIN_NAME);
            if ($this->isPluginDebugMode()) {
                $errorMessage .= '<br/>' . $e->getMessage();
            }
            throw new liveagent_Exception_SignupFail($errorMessage);
        }
        if ($response->success != "Y") {
            throw new liveagent_Exception_SignupFail($response->errorMessage);
        }
    }

    public function trySignup() {
        $name = $_REQUEST[liveagent_Settings::LA_FULL_NAME];
        $domain = $_REQUEST[liveagent_Settings::LA_URL_SETTING_NAME];
        $email = $_REQUEST[liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME];
        $password = substr(md5(microtime()),0,8);

        $tracker = new liveagent_helper_CompactTracker();
        $this->setTrackingData($tracker, $domain);
        $papvisitorId = $tracker->getCookie();

        $this->sendSignupRequest($name, $email, $domain, $password, $papvisitorId);

        update_option(liveagent_Settings::LA_URL_SETTING_NAME, 'http://' . $domain . '.ladesk.com');
        update_option(liveagent_Settings::LA_OWNER_EMAIL_SETTING_NAME, $email);
        update_option(liveagent_Settings::LA_OWNER_PASSWORD_SETTING_NAME, $password);
    }

    private function setTrackingData(liveagent_helper_CompactTracker $tracker, $domain) {
        $tracker->setData1('Wordpress ver.: ' . get_bloginfo('version') . ', domain: ' . $domain);
    }

    public function printButtonsConfigPage() {
        $form = new liveagent_Form_Settings_ButtonCode($this->settings, $this->auth);
        $form->render();
    }
}
?>