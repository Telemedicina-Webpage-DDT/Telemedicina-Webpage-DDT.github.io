<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpLiveAgentPlugin
 *   @version 1.0.0
 *
 *   Licensed under GPL2
 */

class liveagent_helper_CompactTracker extends liveagent_Base {
    const PAP_VISITOR_ID = 'PAPVisitorId';

    private $data1 = null;

    private function getServerName() {
        if (isset($_SERVER["SERVER_NAME"])) {
            return $_SERVER["SERVER_NAME"];
        }
        return 'localhost';
    }

    private function getUrl() {
        if (array_key_exists('PATH_INFO', $_SERVER) && @$_SERVER['PATH_INFO'] != '') {
            $scriptName = str_replace('\\', '/', @$_SERVER['PATH_INFO']);
        } else {
            if (array_key_exists('SCRIPT_NAME', $_SERVER)) {
                $scriptName = str_replace('\\', '/', @$_SERVER['SCRIPT_NAME']);
            } else {
                $scriptName = '';
            }
        }
        $portString = '';
        if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80
        && $_SERVER['SERVER_PORT'] != 443) {
            $portString = ':' . $_SERVER["SERVER_PORT"];
        }
        $protocol = 'http';
        if(isset($_SERVER['HTTPS']) && strlen($_SERVER['HTTPS']) > 0 && strtolower($_SERVER['HTTPS']) != 'off') {
            $protocol = 'https';
        }
        return $protocol . '://' . $this->getServerName() . $portString . $scriptName;
    }

    private function getReferrerUrl() {
        if (array_key_exists('HTTP_REFERER', $_SERVER) && $_SERVER['HTTP_REFERER'] != '') {
            return $_SERVER['HTTP_REFERER'];
        }
        return '';
    }

    private function getIp() {
        return @$_SERVER['REMOTE_ADDR'];
    }

    private function getUserAgent() {
        return @$_SERVER['HTTP_USER_AGENT'];
    }

    public function setData1($data1) {
        $this->data1 = $data1;
    }

    private function getGetParams() {
        $getParams = new La_Net_Http_Request();
        $getParams->addQueryParam('AffiliateID', 'wordpress');
        $getParams->addQueryParam('chan','lawpsignup');
        if ($this->data1 !== null) {
            $getParams->addQueryParam('pd1', $this->data1);
        }
        return $getParams;
    }

    private function getVisitorId($trackingResponse) {
        if ($trackingResponse == '') {
            return null;
        }
        if (!preg_match('/^setVisitor\(\'([a-zA-Z0-9]+)\'\);/', $trackingResponse, $matches)) {
            return null;
        }
        if ($matches[1] != '') {
            return $matches[1];
        }
    }

    private function encodeRefererUrl($url) {
        $url = str_replace('http://', 'H_', $url);
        $url = str_replace('https://', 'S_', $url);
        return $url;
    }

    public function getCookie() {
        if (array_key_exists(self::PAP_VISITOR_ID, $_COOKIE) && $_COOKIE[self::PAP_VISITOR_ID] != '') {
            return $_COOKIE[self::PAP_VISITOR_ID];
        }
        $visitorCookie = $this->getVisitor();
        $this->setVisitorIdCookie($visitorCookie);
        return $visitorCookie;
    }

    private function getVisitor() {
        $request = new La_Net_Http_Request();
        $request->setUrl('http://www.qualityunit.com/affiliate/scripts/track.php');
        $request->setMethod('POST');

        $request->addQueryParam('visitorId', null);
        $request->addQueryParam('accountId', 'default1');
        $request->addQueryParam('url', $this->encodeRefererUrl($this->getUrl()));
        $request->addQueryParam('referrer', $this->encodeRefererUrl($this->getReferrerUrl()));
        $request->addQueryParam('tracking', '1');
        $request->addQueryParam('getParams', $this->getGetParams()->getQuery());
        $request->addQueryParam('cookies', null);
        $request->addQueryParam('ip', $this->getIp());
        $request->addQueryParam('useragent', $this->getUserAgent());
        $request->setUrl($request->getUrl() . $request->getQuery());
        if ($this->isPluginDebugMode()) {
            $request->addQueryParam('PDebug', 'Y');
        }
        $request->setBody("sale=");
        if ($this->isPluginDebugMode()) {
            $this->_log('Tracking request: '.$request->getUrl());
        }
        $client = new La_Net_Http_Client();
        $trackingResponse = trim($client->execute($request)->getBody());
        if ($this->isPluginDebugMode()) {
            $this->_log('Tracking response: '.$trackingResponse);
        }
        return $this->getVisitorId($trackingResponse);
    }

    private function setVisitorIdCookie($visitor) {
        setcookie(self::PAP_VISITOR_ID, $visitor, time() + 315569260, "/");
    }
}