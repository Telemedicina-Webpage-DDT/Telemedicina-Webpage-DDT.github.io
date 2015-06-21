<?php
/**
 *   @copyright Copyright (c) 2012 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */
class liveagent_wordpress_Footer extends liveagent_Base {
    /**
     * @var liveagent_Settings
     */
    private $settings;
    
    public function __construct(liveagent_Settings $settings) {
        $this->settings = $settings;
    }
    
    public function initVisitorFooter() {
        if(is_feed()) {
            return;
        }
        try {
            echo $this->settings->getButtonCode();
        } catch (Exception $e) {
            $this->_log(sprintf('Unable to insert button in footer %s', $e->getMessage()));
        }
    }
}
?>