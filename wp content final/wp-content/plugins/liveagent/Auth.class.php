<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

if (!class_exists('liveagent_Auth')) {
    class liveagent_Auth extends liveagent_Base {
        public function ping($url = null, $supressErrors = false) {            
            $url = $this->getRemoteApiUrl($url);            
            $request = new La_Rpc_DataRequest("Gpf_Common_ConnectionUtil", "ping");
            $request->setUrl($url);
            try {
                $request->sendNow();
            } catch (Exception $e) {                
                if ($supressErrors == false && $this->isPluginDebugMode()) {
                    $this->_log(__('Unable to ping LiveAgent remotelly: ', LIVEAGENT_PLUGIN_NAME) . ' ' . $e->getMessage());
                }
                throw new liveagent_Exception_ConnectProblem();
            }
            $data = $request->getData();
            if ($data->getParam('status') != 'OK') {           
                throw new liveagent_Exception_ConnectProblem();
            }
        }        

        public function tryToLogin($url, $username, $password) {
            $request = new La_Rpc_DataRequest("Gpf_Api_AuthService", "authenticate");
            $request->setField('username', $username);
            $request->setField('password', $password);
            $request->setUrl($this->getRemoteApiUrl($url));

            $this->launchLoginRequest($request);
        }

        private function launchLoginRequest(La_Rpc_DataRequest $request) {
            try {
                $request->sendNow();
            } catch (Exception $e) {
                if ($this->isPluginDebugMode()) {
                    $this->_log(__('Unable to login.', LIVEAGENT_PLUGIN_NAME));
                    $this->_log($e->getMessage());
                }
                throw new liveagent_Exception_ConnectProblem();
            }
            if ($request->getData()->getParam('error')!=null) {
                if ($this->isPluginDebugMode()) {
                    $this->_log(__('Answer from server: ' . print_r($request->getResponseObject()->toObject(), true), LIVEAGENT_PLUGIN_NAME));
                }
                throw new liveagent_Exception_ConnectProblem();
            }
        }

        /**
         * @return La_Rpc_Data
         */
        public function loginAndGetLoginData() {
            $settings = new liveagent_Settings();
             
            $request = new La_Rpc_DataRequest("Gpf_Api_AuthService", "authenticate");

            $request->setField('username' ,$settings->getOwnerEmail());
            $request->setField('password' ,$settings->getOwnerPassword());
            $request->setUrl($this->getRemoteApiUrl());
            
            $this->launchLoginRequest($request);
            return $request->getData();
        }
    }

}
?>