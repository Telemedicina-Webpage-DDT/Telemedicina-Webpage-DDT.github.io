<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_Form_SignupWait extends liveagent_Form_Base {
    /**
     * @var liveagent_Settings
     */
    private $settings;
    
    public function __construct(liveagent_Settings $settings) {
        $this->settings = $settings;        
        parent::__construct(liveagent_Settings::SIGNUP_WAIT_SETTINGS_PAGE_NAME);
    }

    protected function getTemplateFile() {
        return $this->getTemplatesPath() . 'AccountWait.xtpl';
    }

    protected function getType() {
        return liveagent_Form_Base::TYPE_TEMPLATE;
    }

    protected function initForm() {        
        $this->addTranslation('Installing', __('Account installation', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('YourAccountWillBeOnline', __('Your account will be online within the next few minutes. In the meantime, you should recieve a confirmation email including your account credentials.', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('NoteSometimesAccount', __('Note: Sometimes the process account creation may take', LIVEAGENT_PLUGIN_NAME) . '&nbsp;<a href="http://support.qualityunit.com/knowledgebase/live-agent/wordpress-plugin/frequently-asked-questions.html" target="_blank">'.__('a bit longer', LIVEAGENT_PLUGIN_NAME).'</a>');
        $this->addTranslation('initializing', __('Initializing...', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChat', __('LiveAgent - Live chat and helpdesk plugin for Wordpress', LIVEAGENT_PLUGIN_NAME));
        $this->addTranslation('LiveAgentFreeHelpdeskAndLiveChatDescription', __('We want you to enjoy the full functionality of LiveAgent with the trial account.'));
    }
    
    public function render($toVar = false) {
        parent::render($toVar);
        $out = '<script type="text/javascript"><!--//--><![CDATA[//><!--' . "\n";
        $out .= 'runWaitingStatusChanger();' . "\n";
        $out .= 'doPing(\''.$this->settings->getLiveAgentUrl() . '\', true)' . "\n";   
        $out .= '//--><!]]></script>' . "\n";
        echo $out;
    }
}

?>