<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */
class liveagent_AjaxHandler extends liveagent_Base {
    /**
     * @var liveagent_Settings
     */
    private $settings;

    /**
     * @var liveagent_Auth
     */
    private $auth;

    public function __construct(liveagent_Settings $settings, liveagent_Auth $auth) {
        $this->settings = $settings;
        $this->auth = $auth;
    }

    public function liveagentPing() {
        try {
            //we must supress possible errors here because ajaxCall will fail
            $this->auth->ping(null, true);
        } catch (liveagent_Exception_ConnectProblem $e) {
            echo json_encode(array('result'=>0));
            die();
        }

        update_option(liveagent_Settings::ACCOUNT_STATUS, liveagent_Settings::ACCOUNT_STATUS_SET);
        $this->settings->setButtonCode(
                $this->settings->getIntegrationCode($this->settings->getLiveAgentUrl(), liveagent_Settings::DEFAULT_BUTTON_CODE)
        );
        
        echo json_encode(array('result'=>1,
            'runfunction'=>'onPingSuccessfull'));
        die();
    }
}
?>