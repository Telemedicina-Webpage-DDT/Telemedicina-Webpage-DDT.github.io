<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Settings {
    const CACHE_VALIDITY = 600;

    //internal settings
    const INTERNAL_SETTINGS = 'la-settings_internal-settings';
    const OWNER_SESSIONID = 'la-settings_owner-sessionid';
    const OWNER_AUTHTOKEN = 'la-settings_owner-authtoken';
    const ACCOUNT_STATUS = 'la-settings_accountstatus';
    const ACCOUNT_CREATING_DIALOG_LOAD_COUNT = 'la-settings_account-creating-dialog-load-count';

    //general page
    const GENERAL_SETTINGS_PAGE_NAME = 'la-config-general-page';
    const SIGNUP_SETTINGS_PAGE_NAME = 'la-config-signup-page';
    const SIGNUP_WAIT_SETTINGS_PAGE_NAME = 'la-config-signup-wait-page';

    const LA_FULL_NAME = 'la-full-name';
    const LA_URL_SETTING_NAME = 'la-url';
    const LA_OWNER_EMAIL_SETTING_NAME = 'la-owner-email';
    const LA_OWNER_PASSWORD_SETTING_NAME = 'la-owner-password';

    //buttons options
    const BUTTONS_SETTINGS_PAGE_NAME = 'la-config-buttons-page';
    const BUTTONS_CONFIGURATION_SETTING_NAME = 'la-buttons-configuration';
    const BUTTON_CODE = 'la-buttons_buttoncode';

    const NO_AUTH_TOKEN = 'no_auth_token';
    
    //action codes
    const ACTION_CREATE_ACCOUNT = 'createAccount';
    const ACTION_SKIP_CREATE = 'skipCreate';
    const ACTION_CHANGE_ACCOUNT = 'changeAccount';
    const ACTION_RESET_ACCOUNT = 'resetAccount';
    
    const DEFAULT_BUTTON_CODE = 'button1';
    
    //account statuses
    const ACCOUNT_STATUS_NOTSET = 'N';
    const ACCOUNT_STATUS_SET = 'S';
    const ACCOUNT_STATUS_CREATING = 'C';
    
    public function initSettingsForAdminPanel() {
        register_setting(self::GENERAL_SETTINGS_PAGE_NAME, self::LA_URL_SETTING_NAME, array($this, 'sanitizeUrl'));
        register_setting(self::GENERAL_SETTINGS_PAGE_NAME, self::LA_OWNER_EMAIL_SETTING_NAME);
        register_setting(self::GENERAL_SETTINGS_PAGE_NAME, self::LA_OWNER_PASSWORD_SETTING_NAME);
        //only for comaptibility with 1.2.X versions
        register_setting(self::BUTTONS_SETTINGS_PAGE_NAME, self::BUTTONS_CONFIGURATION_SETTING_NAME);
        //
        register_setting(self::BUTTONS_SETTINGS_PAGE_NAME, self::BUTTON_CODE);
        register_setting(self::INTERNAL_SETTINGS, self::OWNER_SESSIONID);
        register_setting(self::INTERNAL_SETTINGS, self::OWNER_AUTHTOKEN);
        register_setting(self::INTERNAL_SETTINGS, self::ACCOUNT_STATUS);
        register_setting(self::INTERNAL_SETTINGS, self::ACCOUNT_CREATING_DIALOG_LOAD_COUNT);
    }
    
    public function getAccountStatus() {
        if (get_option(liveagent_Settings::ACCOUNT_STATUS) == '') {
            return liveagent_Settings::ACCOUNT_STATUS_NOTSET;
        }
        return get_option(liveagent_Settings::ACCOUNT_STATUS);
    }

    public function sanitizeUrl($url) {
        if ($url == null) {
            return '';
        }
        if (stripos($url, 'http://')!==false || stripos($url, 'https://')!==false) {
            return esc_url($url);
        }
        return 'http://' . $url;
    }    
    
    private function setSetting($code, $settingValue) {
        $settings = get_option($code);
        if ($settings != '') {
            update_option($code, $settingValue);
        } else {
            add_option($code, $settingValue);
            update_option($code, $settingValue);
        }
    }

    private function setCachedSetting($code, $value) {
        $settings = get_option($code);
        $settingValue = $value . "||" . time();
        if ($settings != '') {
            update_option($code, $settingValue);
        } else {            
            add_option($code, $settingValue);
            update_option($code, $settingValue);
        }
    }

    private function getCachedSetting($code) {
        $settings = get_option($code);
        if ($settings == null || trim($settings) == '') {
            throw new liveagent_Exception_SettingNotValid(__(sprintf('Setting %s not defined yet.', $code)));
        }
        $settings = explode("||", $settings, 2);
        $validTo = $settings[1] + self::CACHE_VALIDITY + 0;
        if ($validTo > time()) {
            return $settings[0];
        } else {
            if (array_key_exists('time', $settings)) {
                $message = __(sprintf('Setting\'s %s validity exceeded: %s', $code, $settings['time']));
            } else {
                $message = __(sprintf('Setting\'s %s validity exceeded: unknown', $code));
            }
                throw new liveagent_Exception_SettingNotValid($message);
        }
    }

    public function getOwnerSessionId() {
        try {
            return $this->getCachedSetting(self::OWNER_SESSIONID);
        } catch (liveagent_Exception_SettingNotValid $e) {            
            return $this->login();
        }
    }

    public function getOwnerAuthToken() {
        try {
            return $this->getCachedSetting(self::OWNER_AUTHTOKEN);
        } catch (liveagent_Exception_SettingNotValid $e) {
            $this->login();
        }
        try {
            return $this->getCachedSetting(self::OWNER_AUTHTOKEN);
        } catch (liveagent_Exception_SettingNotValid $e) {
            $this->setCachedSetting(self::OWNER_AUTHTOKEN, self::NO_AUTH_TOKEN);
            return self::NO_AUTH_TOKEN;
        }
    }

    private function login() {
        $auth = new liveagent_Auth();
        $loginData = $auth->loginAndGetLoginData();
        try {
            $sessionId = $loginData->getValue('session');
            $this->setCachedSetting(self::OWNER_SESSIONID, $sessionId);
        } catch (La_Data_RecordSetNoRowException $e) {
            throw new liveagent_Exception_ConnectProblem();
        }
        try {
            $this->setCachedSetting(self::OWNER_AUTHTOKEN, $loginData->getValue('authtoken'));
        } catch (La_Data_RecordSetNoRowException $e) {
            // we are communicating with older LA that does not send auth token
            $this->setCachedSetting(self::OWNER_AUTHTOKEN, self::NO_AUTH_TOKEN);
        }
        return $sessionId;
    }
    
    public function setButtonCode($buttonCode) {
        $this->setSetting(self::BUTTON_CODE, $buttonCode);
    }

    public function getLiveAgentUrl() {
        $url = get_option(self::LA_URL_SETTING_NAME);
        if ($url == null) {
            return $url;
        }
        if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
            $url = 'http://' . $url;
        }
        if (strrpos($url, '/') == (strlen($url) - 1)) {
            $url = substr($url, 0, -1);
        }
        return $url; 
    }

    public function getOwnerEmail() {
        return get_option(self::LA_OWNER_EMAIL_SETTING_NAME);
    }

    public function getOwnerPassword() {
        return get_option(self::LA_OWNER_PASSWORD_SETTING_NAME);
    }
    
    public function getButtonCode() {
        $code = get_option(self::BUTTON_CODE);
        if ($code != '') {
            return $code;
        }
        $enabledButtons = get_option(liveagent_Settings::BUTTONS_CONFIGURATION_SETTING_NAME);
        if ($enabledButtons == null) {
            return $code;
        }
        //for compatibility reasons from older versions (1.2.X) 
        foreach ($enabledButtons as $buttonid => $value) {
            $url = $this->getLiveAgentUrl();
            update_option(self::BUTTONS_CONFIGURATION_SETTING_NAME, null);
            $integrationCode = $this->getIntegrationCode($url, $buttonid);
            $this->setButtonCode($integrationCode);
            return $integrationCode;
        }
    }

    public function getIntegrationCode($url, $buttonid) {
        return '<script type="text/javascript" id="la_x2s6df8d" src="'.$url.'/scripts/trackjs.php"></script>' . "\n" .
                '<img src="'.$url.'/scripts/pix.gif" onLoad="LiveAgentTracker.createButton(\''.$buttonid.'\', this);"/>';
    }
}

?>