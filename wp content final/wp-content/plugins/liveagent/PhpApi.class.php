<?php
/**
 *   @copyright Copyright (c) 2010-2011 Quality Unit s.r.o.
 *   @author Quality Unit
 *   @package PhpApi
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 *   Generated on: 2011-06-06 04:17:42
 *   Framework version: 1.5.22
 *
 */

if (!class_exists('La_Lang', false)) {
    class La_Lang {
        public static function _replaceArgs($message, $args = null) {
            if (!is_array($args)) {
                $args = func_get_args();
            }
            if(count($args) > 1 && substr_count($message, '%s') < count($args)) {
                array_shift($args);
                return vsprintf($message, $args);
            }
            return $message;
        }

        public static function _($message, $args = null, $langCode = '') {
            if (!is_array($args)) {
                $args = func_get_args();
            }            
            return self::_replaceArgs($message, $args);
        }

        public static function _sys($message, $args = null) {
            if (!is_array($args)) {
                $args = func_get_args();
            }
            return self::_replaceArgs($message, $args);
        }

        public static function _runtime($message) {
            return $message;
        }

        public static function _localizeRuntime($message, $langCode = '') {
            preg_match_all('/##(.+?)##/ms', $message, $attributes, PREG_OFFSET_CAPTURE);
            foreach ($attributes[1] as $index => $attribute) {
                $message = str_replace($attributes[0][$index][0], self::_($attribute[0], null, $langCode), $message);
            }
            return $message;

        }
    }
}

if (!class_exists('La_Object', false)) {
    class La_Object {
        /**
         * Translate input message into selected language.
         * If translation will not be found, return same message.
         *
         * @param string $message
         * @return string
         */
        public function _($message) {
            $args = func_get_args();
            return La_Lang::_($message, $args);
        }

        /**
         * Translates text enclosed in ##any text##
         * This function is not parsed by language parser, because as input should be used e.g. texts loaded from database
         *
         * @param string $message String to translate
         * @return string Translated text
         */
        public function _localize($message) {
            return La_Lang::_localizeRuntime($message);
        }

        /**
         * Translate input message into default language defined in language settings for account.
         * This function should be used in case message should be translated to default language (e.g. log messages written to event log)
         *
         * @param string $message
         * @return string
         */
        public function _sys($message) {
            $args = func_get_args();
            return La_Lang::_sys($message, $args);
        }
    }

} //end La_Object

if (!interface_exists('La_Controller', false)) {
    interface La_Controller {
        /**
         * @throws La_Controller_Exception_UnsupportedRequest
         */
        public function execute();
    }

} //end La_Controller

if (!interface_exists('La_Rpc_Serializable', false)) {
    interface La_Rpc_Serializable {

        public function toObject();

        public function toText();
    }

} //end La_Rpc_Serializable

if (!interface_exists('La_Rpc_DataEncoder', false)) {
    interface La_Rpc_DataEncoder {
        function encodeResponse(La_Rpc_Serializable $response);
    }



} //end La_Rpc_DataEncoder

if (!interface_exists('La_Rpc_DataDecoder', false)) {
    interface La_Rpc_DataDecoder {
        /**
         * @param string $str
         * @return StdClass
         */
        function decode($str);
    }



} //end La_Rpc_DataDecoder

if (!class_exists('La_Rpc_Array', false)) {
    class La_Rpc_Array extends La_Object implements La_Rpc_Serializable, IteratorAggregate {

        private $array;

        function __construct(array $array = null){
            if($array === null){
                $this->array = array();
            }else{
                $this->array = $array;
            }
        }

        public function add($response) {
            if(is_scalar($response) || $response instanceof La_Rpc_Serializable) {
                $this->array[] = $response;
                return;
            }
            throw new La_Exception("Value of type " . gettype($response) . " is not scalar or La_Rpc_Serializable");
        }

        public function toObject() {
            $array = array();
            foreach ($this->array as $response) {
                if($response instanceof La_Rpc_Serializable) {
                    $array[] = $response->toObject();
                } else {
                    $array[] = $response;
                }
            }
            return $array;
        }

        public function toText() {
            return var_dump($this->array);
        }

        public function getCount() {
            return count($this->array);
        }

        public function get($index) {
            return $this->array[$index];
        }

        /**
         *
         * @return ArrayIterator
         */
        public function getIterator() {
            return new ArrayIterator($this->array);
        }
    }

} //end La_Rpc_Array

if (!class_exists('La_Rpc_Server', false)) {
    class La_Rpc_Server extends La_Object implements La_Controller {
        const REQUESTS = 'R';
        const RUN_METHOD = 'run';
        const FORM_REQUEST = 'FormRequest';
        const FORM_RESPONSE = 'FormResponse';
        const BODY_DATA_NAME = 'D';


        const HANDLER_FORM = 'Y';
        const HANDLER_JASON = 'N';
        const HANDLER_WINDOW_NAME = 'W';

        /**
         * @var La_Rpc_DataEncoder
         */
        private $dataEncoder;
        /**
         * @var La_Rpc_DataDecoder
         */
        private $dataDecoder;

        public function __construct() {
        }

        private function initDatabaseLogger() {
            $logger = La_Log_Logger::getInstance();

            if(!$logger->checkLoggerTypeExists(La_Log_LoggerDatabase::TYPE)) {
                $logger->setGroup(La_Common_String::generateId(10));
                $logLevel = La_Settings::get(La_Settings_Gpf::LOG_LEVEL_SETTING_NAME);
                $logger->add(La_Log_LoggerDatabase::TYPE, $logLevel);
            }
        }

        /**
         * Return response to standard output
         */
        public function execute($request = '') {
            $response = $this->encodeResponse($this->executeRequest($request));
            La_Http::output($response);
        }

        /**
         * @return La_Rpc_Serializable
         */
        public function executeRequest($request = '') {
            try {
                if(isset($_REQUEST[self::BODY_DATA_NAME])) {
                    $request = $this->parseRequestDataFromPost($_REQUEST[self::BODY_DATA_NAME]);
                }
                if($this->isStandardRequestUsed($_REQUEST)) {
                    $request = $this->setStandardRequest();
                }

                $this->setDecoder($request);
                $params = new La_Rpc_Params($this->decodeRequest($request));
                if ($params->getClass() == '' || $params->getMethod() == '') {
                    throw new La_Controller_Exception_UnsupportedRequest();
                }
                $this->setEncoder($params);
                $response = $this->executeRequestParams($params);
            } catch (La_Controller_Exception_UnsupportedRequest $e) {
                throw $e;
            } catch (Exception $e) {
                return new La_Rpc_ExceptionResponse($e);
            }
            return $response;
        }

        private function parseRequestDataFromPost($data) {
            if(get_magic_quotes_gpc()) {
                return stripslashes($data);
            }
            return $data;
        }

        /**
         *
         * @param unknown_type $requestObj
         * @return La_Rpc_Serializable
         */
        private function executeRequestParams(La_Rpc_Params $params) {
            if (La_Application::getInstance()->isInMaintenanceMode()
            && !La_Paths::getInstance()->isInstallModeActive()) {
                return new La_Rpc_MaintenenceModeResponse();
            }
            try {
                La_Db_LoginHistory::logRequest();
                return $this->callServiceMethod($params);
            } catch (La_Session_Exception_SessionExpired $e) {
                return new La_Rpc_SessionExpiredResponse($e);
            } catch (Exception $e) {
                return new La_Rpc_ExceptionResponse($e);
            }
        }

        /**
         * @throws La_Session_Exception_SessionExpired
         */
        protected function callServiceMethod(La_Rpc_Params $params) {
            $method = new La_Rpc_ServiceMethod($params);
            return $method->invoke($params);
        }

        /**
         * Compute correct handler type for server response
         *
         * @param array $requestData
         * @param string $type
         * @return string
         */
        private function getEncoderHandlerType($requestData) {
            if ($this->isFormHandler($requestData, self::FORM_RESPONSE, self::HANDLER_FORM)) {
                return self::HANDLER_FORM;
            }
            if ($this->isFormHandler($requestData, self::FORM_RESPONSE, self::HANDLER_WINDOW_NAME)) {
                return self::HANDLER_WINDOW_NAME;
            }
            return self::HANDLER_JASON;
        }


        private function isFormHandler($requestData, $type, $handler) {
            return (isset($_REQUEST[$type]) && $_REQUEST[$type] == $handler) ||
            (isset($requestData) && isset($requestData[$type]) && $requestData[$type] == $handler);
        }

        private function decodeRequest($requestData) {
            return $this->dataDecoder->decode($requestData);
        }

        private function isStandardRequestUsed($requestArray) {
            return is_array($requestArray) && array_key_exists(La_Rpc_Params::CLASS_NAME, $requestArray);
        }

        private function setStandardRequest() {
            return array_merge($_POST, $_GET);
        }

        private function isFormRequest($request) {
            return $this->isFormHandler($request, self::FORM_REQUEST, self::HANDLER_FORM);
        }

        private function encodeResponse(La_Rpc_Serializable $response) {
            return $this->dataEncoder->encodeResponse($response);
        }


        private function setDecoder($request) {
            if ($this->isFormRequest($request)) {
                $this->dataDecoder = new La_Rpc_FormHandler();
            } else {
                $this->dataDecoder = new La_Rpc_Json();
            }
        }

        private function setEncoder(La_Rpc_Params $params) {
            switch ($params->get(self::FORM_RESPONSE)) {
                case self::HANDLER_FORM:
                    $this->dataEncoder = new La_Rpc_FormHandler();
                    break;
                case self::HANDLER_WINDOW_NAME:
                    $this->dataEncoder = new La_Rpc_WindowNameHandler();
                    break;
                default:
                    $this->dataEncoder = new La_Rpc_Json();
                    break;
            }
        }

        /**
         * Executes multi request
         *
         * @service
         * @anonym
         * @return La_Rpc_Serializable
         */
        public function run(La_Rpc_Params $params) {
            $requestArray = $params->get(self::REQUESTS);

            $response = new La_Rpc_Array();
            foreach ($requestArray as $request) {
                $response->add($this->executeRequestParams(new La_Rpc_Params($request)));
            }
            return $response;
        }

        /**
         * Set time offset between client and server and store it to session
         * Offset is computed as client time - server time
         *
         * @anonym
         * @service
         * @param La_Rpc_Params $params
         * @return La_Rpc_Action
         */
        public function syncTime(La_Rpc_Params $params) {
            $action = new La_Rpc_Action($params);
            La_Module::getProperties()->setTimeOffset($action->getParam('offset')/1000);
            $action->addOk();
            return $action;
        }
    }

} //end La_Rpc_Server

if (!class_exists('La_Rpc_MultiRequest', false)) {
    class La_Rpc_MultiRequest extends La_Object {
        private $url = '';
        /**
         *
         * @var La_Rpc_Array
         */
        private $requests;
        /**
         * @var La_Rpc_Json
         */
        private $json;
        protected $serverClassName = 'Gpf_Rpc_Server';

        private $sessionId = null;

        private $debugRequests = false;

        /**
         * @var La_Rpc_MultiRequest
         */
        private static $instance;

        public function __construct() {
            $this->json = new La_Rpc_Json();
            $this->requests = new La_Rpc_Array();
        }

        /**
         * @return La_Rpc_MultiRequest
         */
        public static function getInstance() {
            if(self::$instance === null) {
                self::$instance = new La_Rpc_MultiRequest();
            }
            return self::$instance;
        }

        public static function setInstance(La_Rpc_MultiRequest $instance) {
            self::$instance = $instance;
        }

        public function add(La_Rpc_Request $request) {
            $this->requests->add($request);
        }

        protected function sendRequest($requestBody) {
            $request = new La_Net_Http_Request();

            $request->setMethod('POST');
            $request->setBody(La_Rpc_Server::BODY_DATA_NAME . '=' . urlencode($requestBody));
            $request->setUrl($this->url);

            $client = new La_Net_Http_Client();
            $response = $client->execute($request);
            return $response->getBody();
        }

        public function setSessionId($sessionId) {
            $this->sessionId = $sessionId;
        }

        public function setDebugRequests($debug) {
            $this->debugRequests = $debug;
        }

        public function send() {
            $request = new La_Rpc_Request($this->serverClassName, La_Rpc_Server::RUN_METHOD);
            $request->addParam(La_Rpc_Server::REQUESTS, $this->requests);
            if($this->sessionId != null) {
                $request->addParam("S", $this->sessionId);
            }
            $requestBody = $this->json->encodeResponse($request);
            $responseText = $this->sendRequest($requestBody);
            $responseArray = $this->json->decode($responseText);
            if (!is_array($responseArray)) {
                throw new La_Exception("Response decoding failed: not array. Received text: $responseText");
            }

            if (count($responseArray) != $this->requests->getCount()) {
                throw new La_Exception("Response decoding failed: Number of responses is not same as number of requests");
            }

            $exception = false;
            foreach ($responseArray as $index => $response) {
                if (is_object($response) && isset($response->e)) {
                    $exception = true;
                    $this->requests->get($index)->setResponseError($response->e);
                } else {
                    $this->requests->get($index)->setResponse($response);
                }
            }
            if($exception) {
                $messages = '';
                foreach ($this->requests as $request) {
                    $messages .= $request->getResponseError() . "|";
                }
            }
            $this->requests = new La_Rpc_Array();
            if($exception) {
                throw new La_Rpc_ExecutionException($messages);
            }
        }

        public function setUrl($url) {
            $this->url = $url;
        }

        public function getUrl() {
            return $this->url;
        }

        private function getCookies() {
            $cookiesString = '';
            foreach ($_COOKIE as $name => $value) {
                $cookiesString .= "$name=$value;";
            }
            return $cookiesString;
        }
    }


} //end La_Rpc_MultiRequest

if (!class_exists('La_Rpc_Params', false)) {
    class La_Rpc_Params extends La_Object implements La_Rpc_Serializable {
        private $params;
        const CLASS_NAME = 'C';
        const METHOD_NAME = 'M';
        const SESSION_ID = 'S';
        const ACCOUNT_ID = 'aid';

        function __construct($params = null) {
            if($params === null) {
                $this->params = new stdClass();
                return;
            }
            $this->params = $params;
        }

        public static function createGetRequest($className, $methodName = 'execute', $formRequest = false, $formResponse = false) {
            $requestData = array();
            $requestData[self::CLASS_NAME] = $className;
            $requestData[self::METHOD_NAME] = $methodName;
            $requestData[La_Rpc_Server::FORM_REQUEST] = $formRequest ? Gpf::YES : '';
            $requestData[La_Rpc_Server::FORM_RESPONSE] = $formResponse ? Gpf::YES : '';
            return $requestData;
        }

        /**
         *
         * @param unknown_type $className
         * @param unknown_type $methodName
         * @param unknown_type $formRequest
         * @param unknown_type $formResponse
         * @return La_Rpc_Params
         */
        public static function create($className, $methodName = 'execute', $formRequest = false, $formResponse = false) {
            $params = new La_Rpc_Params();
            $obj = new stdClass();
            foreach (self::createGetRequest($className, $methodName, $formRequest, $formResponse) as $name => $value) {
                $params->add($name,$value);
            }
            return $params;
        }

        public function setArrayParams(array $params) {
            foreach ($params as $name => $value) {
                $this->add($name, $value);
            }
        }

        public function exists($name) {
            if(!is_object($this->params) || !array_key_exists($name, $this->params)) {
                return false;
            }
            return true;
        }

        /**
         *
         * @param unknown_type $name
         * @return mixed Return null if $name does not exist.
         */
        public function get($name) {
            if(!$this->exists($name)) {
                return null;
            }
            return $this->params->{$name};
        }

        public function set($name, $value) {
            if(!$this->exists($name)) {
                return;
            }
            $this->params->{$name} = $value;
        }

        public function add($name, $value) {
            $this->params->{$name} = $value;
        }

        public function getClass() {
            return $this->get(self::CLASS_NAME);
        }

        public function getMethod() {
            return $this->get(self::METHOD_NAME);
        }

        public function getSessionId() {
            return $this->get(self::SESSION_ID);
        }

        public function clearSessionId() {
            $this->set(self::SESSION_ID, null);
        }

        public function getAccountId() {
            return $this->get(self::ACCOUNT_ID);
        }

        public function toObject() {
            return $this->params;
        }

        public function toText() {
            throw new La_Exception("Unimplemented");
        }
    }


} //end La_Rpc_Params

if (!class_exists('La_Exception', false)) {
    class La_Exception extends Exception {

        private $id;

        public function __construct($message = '',$code = null) {
            $trace = '';
            foreach (debug_backtrace(false) as $i => $traceStep) {
                $trace .= sprintf("#%s - %s::%s() at line %s<br>\n", $i, @$traceStep['class'], @$traceStep['function'], @$traceStep['line']);
            }
            $message .= "<br>\nTRACE:<br>\n" . $trace;
            parent::__construct($message, $code);
        }

        protected function logException() {
            La_Log::error($this->getMessage());
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function getId() {
            return $this->id;
        }

    }

} //end La_Exception

if (!class_exists('La_Data_RecordSetNoRowException', false)) {
    class La_Data_RecordSetNoRowException extends La_Exception {
        public function __construct($keyValue) {
            parent::__construct("'Row $keyValue does not exist");
        }

        protected function logException() {
        }
    }

} //end La_Data_RecordSetNoRowException

if (!class_exists('La_Rpc_ExecutionException', false)) {
    class La_Rpc_ExecutionException extends La_Exception {
        	
        function __construct($message) {
            parent::__construct('RPC Execution exception: ' . $message);
        }
    }

} //end La_Rpc_ExecutionException

if (!class_exists('La_Rpc_Object', false)) {
    class La_Rpc_Object extends La_Object implements La_Rpc_Serializable {

        private $object;

        public function __construct($object = null) {
            $this->object = $object;
        }

        public function toObject() {
            if ($this->object != null) {
                return $this->object;
            }
            return $this;
        }

        public function toText() {
            return var_dump($this);
        }
    }


} //end La_Rpc_Object

if (!class_exists('La_Rpc_Request', false)) {
    class La_Rpc_Request extends La_Object implements La_Rpc_Serializable {
        protected $className;
        protected $methodName;
        private $responseError;
        protected $response;
        protected $apiSessionObject = null;

        /**
         * @var La_Rpc_MultiRequest
         */
        private $multiRequest;

        /**
         * @var La_Rpc_Params
         */
        protected $params;
        private $accountId = null;

        public function __construct($className, $methodName, La_Api_Session $apiSessionObject = null) {
            $this->className = $className;
            $this->methodName = $methodName;
            $this->params = new La_Rpc_Params();
            $this->setRequiredParams($this->className, $this->methodName);
            if($apiSessionObject != null) {
                $this->apiSessionObject = $apiSessionObject;
            }
        }

        public function setAccountId($accountId) {
            $this->accountId = $accountId;
        }

        public function addParam($name, $value) {
            if(is_scalar($value) || is_null($value)) {
                $this->params->add($name, $value);
                return;
            }
            if($value instanceof La_Rpc_Serializable) {
                $this->params->add($name, $value->toObject());
                return;
            }
            throw new La_Exception("Cannot add request param: Value ($name=$value) is not scalar or La_Rpc_Serializable");
        }

        /**
         *
         * @return La_Rpc_MultiRequest
         */
        private function getMultiRequest() {
            if($this->multiRequest === null) {
                return La_Rpc_MultiRequest::getInstance();
            }
            return $this->multiRequest;
        }

        public function setUrl($url) {
            $this->multiRequest = new La_Rpc_MultiRequest();
            $this->multiRequest->setUrl($url);
        }

        public function send() {
            if($this->apiSessionObject != null) {
                $this->multiRequest = new La_Rpc_MultiRequest();
                $this->multiRequest->setUrl($this->apiSessionObject->getUrl());
                $this->multiRequest->setSessionId($this->apiSessionObject->getSessionId());
                $this->multiRequest->setDebugRequests($this->apiSessionObject->getDebug());
            }

            $multiRequest = $this->getMultiRequest();
            $multiRequest->add($this);
        }

        public function sendNow() {
            $this->send();
            $this->getMultiRequest()->send();
        }

        public function setResponseError($message) {
            $this->responseError = $message;
        }

        public function getResponseError() {
            return $this->responseError;
        }

        public function setResponse($response) {
            $this->response = $response;
        }

        public function toObject() {
            return $this->params->toObject();
        }

        public function toText() {
            throw new La_Exception("Unimplemented");
        }

        /**
         *
         * @return stdClass
         */
        final public function getStdResponse() {
            if(isset($this->responseError)) {
                throw new La_Rpc_ExecutionException($this->responseError);
            }
            if($this->response === null) {
                throw new La_Exception("Request not executed yet.");
            }
            return $this->response;
        }

        final public function getResponseObject() {
            return new La_Rpc_Object($this->getStdResponse());
        }

        private function setRequiredParams($className, $methodName) {
            $this->addParam(La_Rpc_Params::CLASS_NAME, $className);
            $this->addParam(La_Rpc_Params::METHOD_NAME, $methodName);
        }

        /**
         * @param La_Rpc_Params $params
         */
        public function setParams(La_Rpc_Params $params) {
            $originalParams = $this->params;
            $this->params = $params;
            $this->setRequiredParams($originalParams->getClass(), $originalParams->getMethod());
        }
    }


} //end La_Rpc_Request

if (!interface_exists('La_HttpResponse', false)) {
    interface La_HttpResponse {
        public function setCookieValue($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null);

        public function setHeaderValue($name, $value, $replace = true, $httpResponseCode = null);

        public function outputText($text);
    }

} //end La_HttpResponse

if (!class_exists('La_Http', false)) {
    class La_Http extends La_Object implements La_HttpResponse {
        /**
         *
         * @var La_HttpResponse
         */
        private static $instance = null;

        /**
         * @return La_Http
         */
        private static function getInstance() {
            if(self::$instance === null) {
                self::$instance = new La_Http();
            }
            return self::$instance;
        }

        public static function setInstance(La_HttpResponse $instance) {
            self::$instance = $instance;
        }

        public static function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null) {
            self::getInstance()->setCookieValue($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }

        public static function setHeader($name, $value, $httpResponseCode = null) {
            self::getInstance()->setHeaderValue($name, $value, true, $httpResponseCode);
        }

        public static function output($text) {
            self::getInstance()->outputText($text);
        }

        public function outputText($text) {
            echo $text;
        }

        public function setHeaderValue($name, $value, $replace = true, $httpResponseCode = null) {
            $fileName = '';
            $line = '';
            if(headers_sent($fileName, $line)) {
                throw new La_Exception("Headers already sent in $fileName line $line while setting header $name: $value");
            }
            header($name . ': ' . $value, $replace, $httpResponseCode);
        }

        public function setCookieValue($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null) {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }

        public static function getCookie($name) {
            if (!array_key_exists($name, $_COOKIE)) {
                return null;
            }
            return $_COOKIE[$name];
        }

        public static function getRemoteIp() {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if (isset($_SERVER['REMOTE_ADDR'])) {
                return $_SERVER['REMOTE_ADDR'];
            }
            return '';
        }

        public static function getRemoteHost(){
            return @gethostbyaddr(self::getRemoteIp());
        }
    }

} //end La_Http

if (!interface_exists('La_Templates_HasAttributes', false)) {
    interface La_Templates_HasAttributes {
        function getAttributes();
    }

} //end La_Templates_HasAttributes

if (!class_exists('La_Data_RecordHeader', false)) {
    class La_Data_RecordHeader extends La_Object {
        private $ids = array();

        /**
         * Create Record header object
         *
         * @param array $headerArray
         */
        public function __construct($headerArray = null) {
            if($headerArray === null) {
                return;
            }

            foreach ($headerArray as $id) {
                $this->add($id);
            }
        }

        public function contains($id) {
            return array_key_exists($id, $this->ids);
        }

        public function add($id) {
            if($this->contains($id)) {
                return;
            }

            $this->ids[$id] = count($this->ids);
        }

        public function getIds() {
            return array_keys($this->ids);
        }

        public function getIndex($id) {
            if(!$this->contains($id)) {
                throw new La_Exception("Unknown column '" . $id ."'");
            }
            return $this->ids[$id];
        }

        public function getSize() {
            return count($this->ids);
        }

        public function toArray() {
            $response = array();
            foreach ($this->ids as $columnId => $columnIndex) {
                $response[] = $columnId;
            }
            return $response;
        }

        public function toObject() {
            $result = array();
            foreach ($this->ids as $columnId => $columnIndex) {
                $result[] = $columnId;
            }
            return $result;
        }
    }


} //end La_Data_RecordHeader

if (!interface_exists('La_Data_Row', false)) {
    interface La_Data_Row {
        public function get($name);

        public function set($name, $value);
    }

} //end La_Data_Row

if (!class_exists('La_Data_Record', false)) {
    class La_Data_Record extends La_Object implements Iterator, La_Rpc_Serializable,
    La_Templates_HasAttributes, La_Data_Row {
        private $record;
        /**
         *
         * @var La_Data_RecordHeader
         */
        private $header;
        private $position;

        /**
         * Create record
         *
         * @param array $header
         * @param array $array values of record from array
         */
        public function __construct($header, $array = array()) {
            if (is_array($header)) {
                $header = new La_Data_RecordHeader($header);
            }
            $this->header = $header;
            $this->record = array_values($array);
            while(count($this->record) < $this->header->getSize()) {
                $this->record[] = null;
            }
        }

        function getAttributes() {
            $ret = array();
            foreach ($this as $name => $value) {
                $ret[$name] = $value;
            }
            return $ret;
        }

        public function contains($id) {
            return $this->header->contains($id);
        }

        public function get($id) {
            $index = $this->header->getIndex($id);
            return $this->record[$index];
        }

        public function set($id, $value) {
            $index = $this->header->getIndex($id);
            $this->record[$index] = $value;
        }

        public function add($id, $value) {
            $this->header->add($id);
            $this->set($id, $value);
        }

        public function toObject() {
            return $this->record;
        }

        public function loadFromObject(array $array) {
            $this->record = $array;
        }

        public function toText() {
            return implode('-', $this->record);
        }

        public function current() {
            if(!isset($this->record[$this->position])) {
                return null;
            }
            return $this->record[$this->position];
        }

        public function key() {
            $ids = $this->header->getIds();
            return $ids[$this->position];
        }

        public function next() {
            $this->position++;
        }

        public function rewind() {
            $this->position = 0;
        }

        public function valid() {
            return $this->position < $this->header->getSize();
        }
    }


} //end La_Data_Record

if (!class_exists('La_Data_Grid', false)) {
    class La_Data_Grid extends La_Object {
        /**
         * @var La_Data_RecordSet
         */
        private $recordset;
        private $totalCount;

        public function loadFromObject(stdClass  $object) {
            $this->recordset = new La_Data_RecordSet();
            $this->recordset->loadFromObject($object->R);
            $this->totalCount = $object->C;
        }

        /**
         * @return La_Data_RecordSet
         */
        public function getRecordset() {
            return $this->recordset;
        }

        public function getTotalCount() {
            return $this->totalCount;
        }
    }


} //end La_Data_Grid

if (!class_exists('La_Data_Filter', false)) {
    class La_Data_Filter extends La_Object implements La_Rpc_Serializable {
        const LIKE = "L";
        const NOT_LIKE = "NL";
        const EQUALS = "E";
        const NOT_EQUALS = "NE";

        const DATE_EQUALS = "D=";
        const DATE_GREATER = "D>";
        const DATE_LOWER = "D<";
        const DATE_EQUALS_GREATER = "D>=";
        const DATE_EQUALS_LOWER = "D<=";
        const DATERANGE_IS = "DP";
        const TIME_EQUALS = "T=";
        const TIME_GREATER = "T>";
        const TIME_LOWER = "T<";
        const TIME_EQUALS_GREATER = "T>=";
        const TIME_EQUALS_LOWER = "T<=";

        const RANGE_TODAY = 'T';
        const RANGE_YESTERDAY = 'Y';
        const RANGE_LAST_7_DAYS = 'L7D';
        const RANGE_LAST_30_DAYS = 'L30D';
        const RANGE_LAST_90_DAYS = 'L90D';
        const RANGE_THIS_WEEK = 'TW';
        const RANGE_LAST_WEEK = 'LW';
        const RANGE_LAST_2WEEKS = 'L2W';
        const RANGE_LAST_WORKING_WEEK = 'LWW';
        const RANGE_THIS_MONTH = 'TM';
        const RANGE_LAST_MONTH = 'LM';
        const RANGE_THIS_YEAR = 'TY';
        const RANGE_LAST_YEAR = 'LY';

        private $code;
        private $operator;
        private $value;
        	
        public function __construct($code, $operator, $value) {
            $this->code = $code;
            $this->operator = $operator;
            $this->value = $value;
        }
        	
        public function toObject() {
            return array($this->code, $this->operator, $this->value);
        }
        	
        public function toText() {
            throw new La_Exception("Unsupported");
        }
    }


} //end La_Data_Filter

if (!class_exists('La_Rpc_GridRequest', false)) {
    class La_Rpc_GridRequest extends La_Rpc_Request {

        private $filters = array();
        	
        private $limit = '';
        private $offset = '';
        	
        private $sortColumn = '';
        private $sortAscending = false;
        	
        /**
         * @return La_Data_Grid
         */
        public function getGrid() {
            $response = new La_Data_Grid();
            $response->loadFromObject($this->getStdResponse());
            return $response;
        }

        public function getFilters() {
            return $this->filters;
        }

        /**
         * adds filter to grid
         *
         * @param unknown_type $code
         * @param unknown_type $operator
         * @param unknown_type $value
         */
        public function addFilter($code, $operator, $value) {
            $this->filters[] = new La_Data_Filter($code, $operator, $value);
        }

        public function setLimit($offset, $limit) {
            $this->offset = $offset;
            $this->limit = $limit;
        }

        public function setSorting($sortColumn, $sortAscending = false) {
            $this->sortColumn = $sortColumn;
            $this->sortAscending = $sortAscending;
        }

        public function send() {
            if(count($this->filters) > 0) {
                $this->addParam("filters", $this->addFiltersParameter());
            }
            if($this->sortColumn !== '') {
                $this->addParam("sort_col", $this->sortColumn);
                $this->addParam("sort_asc", ($this->sortAscending ? 'true' : 'false'));
            }
            if($this->offset !== '') {
                $this->addParam("offset", $this->offset);
            }
            if($this->limit !== '') {
                $this->addParam("limit", $this->limit);
            }

            parent::send();
        }

        private function addFiltersParameter() {
            $filters = new La_Rpc_Array();

            foreach($this->filters as $filter) {
                $filters->add($filter);
            }

            return $filters;
        }
    }



} //end La_Rpc_GridRequest

if (!class_exists('La_Data_RecordSet', false)) {
    class La_Data_RecordSet extends La_Object implements IteratorAggregate, La_Rpc_Serializable {

        const SORT_ASC = 'ASC';
        const SORT_DESC = 'DESC';

        protected $_array;
        /**
         * @var La_Data_RecordHeader
         */
        private $_header;

        function __construct() {
            $this->init();
        }

        public function loadFromArray($rows) {
            $this->setHeader($rows[0]);

            for ($i = 1; $i < count($rows); $i++) {
                $this->add($rows[$i]);
            }
        }

        public function setHeader($header) {
            if($header instanceof La_Data_RecordHeader) {
                $this->_header = $header;
                return;
            }
            $this->_header = new La_Data_RecordHeader($header);
        }

        /**
         * @return La_Data_RecordHeader
         */
        public function getHeader() {
            return $this->_header;
        }

        public function addRecordAtStart(La_Data_Record $record) {
            array_unshift($this->_array, $record);
        }

        public function addRecord(La_Data_Record $record) {
            $this->_array[] = $record;
        }

        /**
         * Adds new row to RecordSet
         *
         * @param array $record array of data for all columns in record
         */
        public function add($record) {
            $this->addRecord($this->getRecordObject($record));
        }

        /**
         * @return La_Data_Record
         */
        public function createRecord() {
            return new La_Data_Record($this->_header);
        }

        public function toObject() {
            $response = array();
            $response[] = $this->_header->toObject();
            foreach ($this->_array as $record) {
                $response[] = $record->toObject();
            }
            return $response;
        }

        public function loadFromObject(array $array) {
            $this->_header = new La_Data_RecordHeader($array[0]);
            for($i = 1; $i < count($array);$i++) {
                $record = new La_Data_Record($this->_header);
                $record->loadFromObject($array[$i]);
                $this->loadRecordFromObject($record);
            }
        }

        public function sort($column, $sortType = 'ASC') {
            if (!$this->_header->contains($column)) {
                throw new La_Exception('Undefined column');
            }
            $sorter = new La_Data_RecordSet_Sorter($column, $sortType);
            $this->_array = $sorter->sort($this->_array);
        }

        protected function loadRecordFromObject(La_Data_Record $record) {
            $this->_array[] = $record;
        }

        public function toArray() {
            $response = array();
            foreach ($this->_array as $record) {
                $response[] = $record->getAttributes();
            }
            return $response;
        }

        public function toText() {
            $text = '';
            foreach ($this->_array as $record) {
                $text .= $record->toText() . "<br>\n";
            }
            return $text;
        }

        /**
         * Return number of rows in recordset
         *
         * @return integer
         */
        public function getSize() {
            return count($this->_array);
        }

        /**
         * @return La_Data_Record
         */
        public function get($i) {
            return $this->_array[$i];
        }

        /**
         * @param array/La_Data_Record $record
         * @return La_Data_Record
         */
        private function getRecordObject($record) {
            if(!($record instanceof La_Data_Record)) {
                $record = new La_Data_Record($this->_header->toArray(), $record);
            }
            return $record;
        }

        private function init() {
            $this->_array = array();
            $this->_header = new La_Data_RecordHeader();
        }

        public function clear() {
            $this->init();
        }
        
        public function load(La_SqlBuilder_SelectBuilder $select) {
        }
        
        /**
         *
         * @return ArrayIterator
         */
        public function getIterator() {
            return new ArrayIterator($this->_array);
        }

        public function getRecord($keyValue) {
            if(!array_key_exists($keyValue, $this->_array)) {
                return $this->createRecord();
            }
            return $this->_array[$keyValue];
        }

        public function addColumn($id, $defaultValue = "") {
            $this->_header->add($id);
            foreach ($this->_array as $record) {
                $record->add($id, $defaultValue);
            }
        }

        /**
         * Creates shalow copy of recordset containing only headers
         *
         * @return La_Data_RecordSet
         */
        public function toShalowRecordSet() {
            $copy = new La_Data_RecordSet();
            $copy->setHeader($this->_header->toArray());
            return $copy;
        }
    }

    class La_Data_RecordSet_Sorter {

        private $sortColumn;
        private $sortType;

        function __construct($column, $sortType) {
            $this->sortColumn = $column;
            $this->sortType = $sortType;
        }

        public function sort(array $sortedArray) {
            usort($sortedArray, array($this, 'compareRecords'));
            return $sortedArray;
        }

        private function compareRecords($record1, $record2) {
            if ($record1->get($this->sortColumn) == $record2->get($this->sortColumn)) {
                return 0;
            }
            return $this->compare($record1->get($this->sortColumn), $record2->get($this->sortColumn));
        }

        private function compare($value1, $value2) {
            if ($this->sortType == La_Data_RecordSet::SORT_ASC) {
                return ($value1 < $value2) ? -1 : 1;
            }
            return ($value1 < $value2) ? 1 : -1;
        }
    }

} //end La_Data_RecordSet

if (!class_exists('La_Data_IndexedRecordSet', false)) {
    class La_Data_IndexedRecordSet extends La_Data_RecordSet {
        private $key;

        /**
         *
         * @param int $keyIndex specifies which column should be used as a key
         */
        function __construct($key) {
            parent::__construct();
            $this->key = $key;
        }

        public function addRecord(La_Data_Record $record) {
            $this->_array[$record->get($this->key)] = $record;
        }

        /**
         * @param String $keyValue
         * @return La_Data_Record
         */
        public function createRecord($keyValue = null) {
            if($keyValue === null) {
                return parent::createRecord();
            }
            if(!array_key_exists($keyValue, $this->_array)) {
                $record = $this->createRecord();
                $record->set($this->key, $keyValue);
                $this->addRecord($record);
            }
            return $this->_array[$keyValue];
        }

        protected function loadRecordFromObject(La_Data_Record $record) {
            $this->_array[$record->get($this->key)] = $record;
        }

        /**
         * @param String $keyValue
         * @return La_Data_Record
         */
        public function getRecord($keyValue = null) {
            if (!isset($this->_array[$keyValue])) {
                throw new La_Data_RecordSetNoRowException($keyValue);
            }
            return $this->_array[$keyValue];
        }

        /**
         * @param String $keyValue
         * @return boolean
         */
        public function existsRecord($keyValue) {
            return isset($this->_array[$keyValue]);
        }

        /**
         * @param String $sortOptions (SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING)
         * @return boolean
         */
        public function sortByKeyValue($sortOptions) {
            return array_multisort($this->_array, $sortOptions);
        }
    }


} //end La_Data_IndexedRecordSet

if (!class_exists('La_Net_Http_Request', false)) {
    class La_Net_Http_Request extends La_Object {
        const CRLF = "\r\n";

        private $method = 'GET';
        private $url;

        //proxy server
        private $proxyServer = '';
        private $proxyPort = '';
        private $proxyUser = '';
        private $proxyPassword = '';

        //URL components
        private $scheme = 'http';
        private $host = '';
        private $port = 80;
        private $http_user = '';
        private $http_password = '';
        private $path = '';
        private $query = '';
        private $fragment = '';
        private $cookies = '';

        private $body = '';
        private $headers = array();

        public function setCookies($cookies) {
            $this->cookies = $cookies;
        }

        public function getCookies() {
            return $this->cookies;
        }

        public function getCookiesHeader() {
            return "Cookie: " . $this->cookies;
        }

        public function setUrl($url) {
            $this->url = $url;
            $this->parseUrl();
        }

        public function getUrl() {
            return $this->url;
        }

        private function parseUrl() {
            $components = @parse_url($this->url);
            if (!$components) {
                return;
            }
            if (array_key_exists('scheme', $components)) {
                $this->scheme = $components['scheme'];
            }
            if (array_key_exists('host', $components)) {
                $this->host = $components['host'];
            }
            if (array_key_exists('port', $components)) {
                $this->port = $components['port'];
            }
            if (array_key_exists('user', $components)) {
                $this->http_user = $components['user'];
            }
            if (array_key_exists('pass', $components)) {
                $this->http_password = $components['pass'];
            }
            if (array_key_exists('path', $components)) {
                $this->path = $components['path'];
            }
            if (array_key_exists('query', $components)) {
                $this->query = $components['query'];
            }
            if (array_key_exists('fragment', $components)) {
                $this->fragement = $components['fragment'];
            }
        }

        public function getScheme() {
            return $this->scheme;
        }

        public function getHost() {
            if (strlen($this->proxyServer)) {
                return $this->proxyServer;
            }
            return $this->host;
        }

        public function getPort() {
            if (strlen($this->proxyServer)) {
                return $this->proxyPort;
            }

            if (strlen($this->port)) {
                return $this->port;
            }
            return 80;
        }

        public function getHttpUser() {
            return $this->http_user;
        }
        	
        public function setHttpUser($user) {
            $this->http_user = $user;
        }

        public function getHttpPassword() {
            return $this->http_password;
        }
        	
        public function setHttpPassword($pass) {
            $this->http_password = $pass;
        }

        public function getPath() {
            return $this->path;
        }

        public function getQuery() {
            return $this->query;
        }

        public function addQueryParam($name, $value) {
            if (is_array($value)) {
                foreach($value as $key => $subValue) {
                    $this->addQueryParam($name."[".$key."]", $subValue);
                }
                return;
            }
            $this->query .= ($this->query == '') ? '?' : '&';
            $this->query .= $name.'='.urlencode($value);
        }

        public function getFragemnt() {
            return $this->fragment;
        }

        /**
         * Set if request method is GET or POST
         *
         * @param string $method possible values are POST or GET
         */
        public function setMethod($method) {
            $method = strtoupper($method);
            if ($method != 'GET' && $method != 'POST') {
                throw new La_Exception('Unsupported HTTP method: ' . $method);
            }
            $this->method = $method;
        }

        /**
         * get the request method
         *
         * @access   public
         * @return   string
         */
        public function getMethod() {
            return $this->method;
        }

        /**
         * In case request should be redirected through proxy server, set proxy server settings
         * This function should be called after function setHost !!!
         *
         * @param string $server
         * @param string $port
         * @param string $user
         * @param string $password
         */
        public function setProxyServer($server, $port, $user, $password) {
            $this->proxyServer = $server;
            $this->proxyPort = $port;
            $this->proxyUser = $user;
            $this->proxyPassword = $password;
        }

        public function getProxyServer() {
            return $this->proxyServer;
        }

        public function getProxyPort() {
            return $this->proxyPort;
        }

        public function getProxyUser() {
            return $this->proxyUser;
        }

        public function getProxyPassword() {
            return $this->proxyPassword;
        }

        public function setBody($body) {
            $this->body = $body;
        }

        public function getBody() {
            return $this->body;
        }

        /**
         * Set header value
         *
         * @param string $name
         * @param string $value
         */
        public function setHeader($name, $value) {
            $this->headers[$name] = $value;
        }

        /**
         * Get header value
         *
         * @param string $name
         * @return string
         */
        public function getHeader($name) {
            if (array_key_exists($name, $this->headers)) {
                return $this->headers[$name];
            }
            return null;
        }

        /**
         * Return array of headers
         *
         * @return array
         */
        public function getHeaders() {
            $headers = array();
            foreach ($this->headers as $headerName => $headerValue) {
                $headers[] = "$headerName: $headerValue";
            }
            return $headers;
        }

        private function initHeaders() {
            if ($this->getPort() == '80') {
                $this->setHeader('Host', $this->getHost());
            } else {
                $this->setHeader('Host', $this->getHost() . ':' . $this->getPort());
            }
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $this->setHeader('User-Agent', $_SERVER['HTTP_USER_AGENT']);
            }
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->setHeader('Accept', $_SERVER['HTTP_ACCEPT']);
            }
            if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
                $this->setHeader('Accept-Charset', $_SERVER['HTTP_ACCEPT_CHARSET']);
            }
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $this->setHeader('Accept-Language', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
            if (isset($_SERVER['HTTP_REFERER'])) {
                $this->setHeader('Referer', $_SERVER['HTTP_REFERER']);
            }
            if ($this->getMethod() == 'POST' && !strlen($this->getHeader("Content-Type"))) {
                $this->setHeader("Content-Type", "application/x-www-form-urlencoded");
            }

            $this->setHeader('Content-Length', strlen($this->getBody()));
            $this->setHeader('Connection', 'close');

            if (strlen($this->proxyUser)) {
                $this->setHeader('Proxy-Authorization',
              'Basic ' . base64_encode ($this->proxyUser . ':' . $this->proxyPassword));
            }

        }

        public function getUri() {
            $uri = $this->getPath();
            if (strlen($this->getQuery())) {
                $uri .= '?' . $this->getQuery();
            }
            return $uri;
        }

        public function toString() {
            $this->initHeaders();
            $out = sprintf('%s %s HTTP/1.0' . self::CRLF, $this->getMethod(), $this->getUri());
            $out .= implode(self::CRLF, $this->getHeaders()) . self::CRLF . $this->getCookiesHeader() . self::CRLF;
            $out .= self::CRLF . $this->getBody();
            return $out;
        }

    }

} //end La_Net_Http_Request

if (!class_exists('La_Net_Http_ClientBase', false)) {
    abstract class La_Net_Http_ClientBase extends La_Object {
        const CONNECTION_TIMEOUT = 20;

        //TODO: rename this method to "send()"
        /**
        * @param La_Net_Http_Request $request
        * @return La_Net_Http_Response
        */
        public function execute(La_Net_Http_Request $request) {

            if (!$this->isNetworkingEnabled()) {
                throw new La_Exception($this->_('Network connections are disabled'));
            }

            if (!strlen($request->getUrl())) {
                throw new La_Exception('No URL defined.');
            }

            $this->setProxyServer($request);
            if (La_Php::isFunctionEnabled('curl_init')) {
                return $this->executeWithCurl($request);
            } else {
                return $this->executeWithSocketOpen($request);
            }
        }

        protected abstract function isNetworkingEnabled();

        /**
         * @param La_Net_Http_Request $request
         * @return La_Net_Http_Response
         */
        private function executeWithSocketOpen(La_Net_Http_Request $request) {
            $scheme = ($request->getScheme() == 'ssl' || $request->getScheme() == 'https') ? 'ssl://' : '';
            $proxySocket = @fsockopen($scheme . $request->getHost(), $request->getPort(), $errorNr,
            $errorMessage, self::CONNECTION_TIMEOUT);

            if($proxySocket === false) {
                $gpfErrorMessage = $this->_sys('Could not connect to server: %s:%s, Failed with error: %s', $request->getHost(), $request->getPort(), $errorMessage);
                La_Log::error($gpfErrorMessage);
                throw new La_Exception($gpfErrorMessage);
            }

            $requestText = $request->toString();

            $result = @fwrite($proxySocket, $requestText);
            if($result === false || $result != strlen($requestText)) {
                @fclose($proxySocket);
                $gpfErrorMessage = $this->_sys('Could not send request to server %s:%s', $request->getHost(), $request->getPort());
                La_Log::error($gpfErrorMessage);
                throw new La_Exception($gpfErrorMessage);
            }

            $result = '';
            while (false === @feof($proxySocket)) {
                try {
                    if(false === ($data = @fread($proxySocket, 8192))) {
                        La_Log::error($this->_sys('Could not read from proxy socket'));
                        throw new La_Exception("could not read from proxy socket");
                    }
                    $result .= $data;
                } catch (Exception $e) {
                    La_Log::error($this->_sys('Proxy failed: %s', $e->getMessage()));
                    @fclose($proxySocket);
                    throw new La_Exception($this->_('Proxy failed: %s', $e->getMessage()));
                }
            }
            @fclose($proxySocket);

            $response = new La_Net_Http_Response();
            $response->setResponseText($result);

            return $response;
        }


        /**
         * @param La_Net_Http_Request $request
         * @return La_Net_Http_Response
         *      */
        private function executeWithCurl(La_Net_Http_Request $request) {
            $session = curl_init($request->getUrl());

            if ($request->getMethod() == 'POST') {
                @curl_setopt ($session, CURLOPT_POST, true);
                @curl_setopt ($session, CURLOPT_POSTFIELDS, $request->getBody());
            }

            $cookies = $request->getCookies();
            if($cookies) {
                @curl_setopt($session, CURLOPT_COOKIE, $cookies);
            }

            @curl_setopt($session, CURLOPT_HEADER, true);
            @curl_setopt($session, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT);
            @curl_setopt($session, CURLOPT_HTTPHEADER, $request->getHeaders());
            @curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            if ($request->getHttpPassword() != '' && $request->getHttpUser() != '') {
                @curl_setopt($session, CURLOPT_USERPWD, $request->getHttpUser() . ":" . $request->getHttpPassword());
                @curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            }
            @curl_setopt ($session, CURLOPT_SSL_VERIFYHOST, 0);
            @curl_setopt ($session, CURLOPT_SSL_VERIFYPEER, 0);

            $this->setupCurlProxyServer($session, $request);

            // Make the call
            $result = curl_exec($session);
            $error = curl_error($session);

            curl_close($session);

            if (strlen($error)) {
                throw new La_Exception("Curl error: " . $error);
            }

            $response = new La_Net_Http_Response();
            $response->setResponseText($result);

            return $response;
        }

        protected function setProxyServer(La_Net_Http_Request $request) {
            $request->setProxyServer('', '', '', '');
        }

        private function setupCurlProxyServer($curlSession, La_Net_Http_Request $request) {
            if (strlen($request->getProxyServer()) && strlen($request->getProxyPort())) {
                @curl_setopt($curlSession, CURLOPT_PROXY, $request->getProxyServer() . ':' . $request->getProxyPort());
                if (strlen($request->getProxyUser())) {
                    @curl_setopt($curlSession, CURLOPT_PROXYUSERPWD, $request->getProxyUser() . ':' . $request->getProxyPassword());
                }
            }
        }
    }

} //end La_Net_Http_ClientBase

if (!class_exists('La_Net_Http_Response', false)) {
    class La_Net_Http_Response extends La_Object {

        private $responseText = '';
        private $header = '';
        private $body = '';

        public function setResponseText($responseText) {
            $this->responseText = $responseText;
            $this->parse();
        }

        public function getHeadersText() {
            return $this->header;
        }

        private function getHeaderPosition($pos) {
            return strpos($this->responseText, "\r\n\r\nHTTP", $pos);
        }

        public function getBody() {
            return $this->body;
        }

        private function parse() {
            $offset = 0;
            while ($this->getHeaderPosition($offset)) {
                $offset = $this->getHeaderPosition($offset) + 4;
            }
            if (($pos = strpos($this->responseText, "\r\n\r\n", $offset)) > 0) {
                $this->body = substr($this->responseText, $pos + 4);
                $this->header = substr($this->responseText, $offset, $pos - $offset);
                return;
            }
            $this->body = '';
            $this->header = '';
        }
    }

} //end La_Net_Http_Response

if (!class_exists('La_Rpc_Form', false)) {
    class La_Rpc_Form extends La_Object implements La_Rpc_Serializable, IteratorAggregate {
        const FIELD_NAME  = "name";
        const FIELD_VALUE = "value";
        const FIELD_ERROR = "error";
        const FIELD_VALUES = "values";

        private $isError = false;
        private $errorMessage = "";
        private $infoMessage = "";
        private $status;
        /**
         * @var La_Data_IndexedRecordSet
         */
        private $fields;
        /**
         * @var La_Rpc_Form_Validator_FormValidatorCollection
         */
        private $validators;

        public function __construct(La_Rpc_Params $params = null) {
            $this->fields = new La_Data_IndexedRecordSet(self::FIELD_NAME);

            $header = new La_Data_RecordHeader();
            $header->add(self::FIELD_NAME);
            $header->add(self::FIELD_VALUE);
            $header->add(self::FIELD_VALUES);
            $header->add(self::FIELD_ERROR);
            $this->fields->setHeader($header);

            $this->validator = new La_Rpc_Form_Validator_FormValidatorCollection($this);

            if($params) {
                $this->loadFieldsFromArray($params->get("fields"));
            }
        }

        /**
         * @param $validator
         * @param $fieldName
         * @param $fieldLabel
         */
        public function addValidator(La_Rpc_Form_Validator_Validator $validator, $fieldName, $fieldLabel = null) {
            $this->validator->addValidator($validator, $fieldName, $fieldLabel);
        }

        /**
         * @return boolean
         */
        public function validate() {
            return $this->validator->validate();
        }

        public function loadFieldsFromArray($fields) {
            for ($i = 1; $i < count($fields); $i++) {
                $field = $fields[$i];
                $this->fields->add($field);
            }
        }

        /**
         *
         * @return ArrayIterator
         */
        public function getIterator() {
            return $this->fields->getIterator();
        }

        public function addField($name, $value) {
            $record = $this->fields->createRecord($name);
            $record->set(self::FIELD_VALUE, $value);
        }

        public function setField($name, $value, $values = null, $error = "") {
            $record = $this->fields->createRecord($name);
            $record->set(self::FIELD_VALUE, $value);
            $record->set(self::FIELD_VALUES, $values);
            $record->set(self::FIELD_ERROR, $error);
        }

        public function setFieldError($name, $error) {
            $this->isError = true;
            $record = $this->fields->getRecord($name);
            $record->set(self::FIELD_ERROR, $error);
        }

        public function getFieldValue($name) {
            $record = $this->fields->getRecord($name);
            return $record->get(self::FIELD_VALUE);
        }

        public function getFieldError($name) {
            $record = $this->fields->getRecord($name);
            return $record->get(self::FIELD_ERROR);
        }

        public function existsField($name) {
            return $this->fields->existsRecord($name);
        }

        public function load(La_Data_Row $row) {
            foreach($row as $columnName => $columnValue) {
                $this->setField($columnName, $row->get($columnName));
            }
        }

        /**
         * @return La_Data_IndexedRecordSet
         */
        public function getFields() {
            return $this->fields;
        }

        public function fill(La_Data_Row $row) {
            foreach ($this->fields as $field) {
                try {
                    $row->set($field->get(self::FIELD_NAME), $field->get(self::FIELD_VALUE));
                } catch (Exception $e) {
                }
            }
        }

        public function toObject() {
            $response = new stdClass();
            $response->F = $this->fields->toObject();
            if ($this->isSuccessful()) {
                $response->S = Gpf::YES;
                $response->M = $this->infoMessage;
            } else {
                $response->S = Gpf::NO;
                $response->M = $this->errorMessage;
            }
            if (!strlen($response->M)) {
                unset($response->M);
            }
            return $response;
        }

        public function loadFromObject(stdClass $object) {
            if ($object->success == Gpf::YES) {
                $this->setInfoMessage($object->message);
            } else {
                $this->setErrorMessage($object->message);
            }

            $this->fields = new La_Data_IndexedRecordSet(self::FIELD_NAME);
            $this->fields->loadFromObject($object->fields);
        }

        public function toText() {
            return var_dump($this->toObject());
        }

        public function setErrorMessage($message) {
            $this->isError = true;
            $this->errorMessage = $message;
        }

        public function getErrorMessage() {
            if ($this->isError) {
                return $this->errorMessage;
            }
            return "";
        }

        public function setInfoMessage($message) {
            $this->infoMessage = $message;
        }

        public function setSuccessful() {
            $this->isError = false;
        }

        public function getInfoMessage() {
            if ($this->isError) {
                return "";
            }
            return $this->infoMessage;
        }


        /**
         * @return boolean
         */
        public function isSuccessful() {
            return !$this->isError;
        }

        /**
         * @return boolean
         */
        public function isError() {
            return $this->isError;
        }

        public function getDefaultErrorMessage() {
            return $this->_('There were errors, please check the highlighted fields');
        }
    }


} //end La_Rpc_Form

if (!class_exists('La_Rpc_Form_Validator_FormValidatorCollection', false)) {
    class La_Rpc_Form_Validator_FormValidatorCollection extends La_Object {

        /**
         * @var array<La_Rpc_Form_Validator_FieldValidator>
         */
        private $validators;
        /**
         * @var La_Rpc_Form
         */
        private $form;

        public function __construct(La_Rpc_Form $form) {
            $this->form = $form;
            $this->validators = array();
        }

        /**
         * @param $fieldName
         * @param $validator
         */
        public function addValidator(La_Rpc_Form_Validator_Validator $validator, $fieldName, $fieldLabel = null) {
            if (!array_key_exists($fieldName, $this->validators)) {
                $this->validators[$fieldName] = new La_Rpc_Form_Validator_FieldValidator(($fieldLabel === null ? $fieldName : $fieldLabel));
            }
            $this->validators[$fieldName]->addValidator($validator);
        }

        /**
         * @return boolean
         */
        public function validate() {
            $errorMsg = false;
            foreach ($this->validators as $fieldName => $fieldValidator) {
                if (!$fieldValidator->validate($this->form->getFieldValue($fieldName))) {
                    $errorMsg = true;
                    $this->form->setFieldError($fieldName, $fieldValidator->getMessage());
                }
            }
            if ($errorMsg) {
                $this->form->setErrorMessage($this->form->getDefaultErrorMessage());
            }
            return !$errorMsg;
        }
    }

} //end La_Rpc_Form_Validator_FormValidatorCollection

if (!class_exists('La_Rpc_FormRequest', false)) {
    class La_Rpc_FormRequest extends La_Rpc_Request {
        /**
         * @var La_Rpc_Form
         */
        private $fields;

        public function __construct($className, $methodName, La_Api_Session $apiSessionObject = null) {
            parent::__construct($className, $methodName, $apiSessionObject);
            $this->fields = new La_Rpc_Form();
        }

        public function send() {
            $this->addParam('fields', $this->fields->getFields());
            parent::send();
        }

        /**
         * @return La_Rpc_Form
         */
        public function getForm() {
            $response = new La_Rpc_Form();
            $response->loadFromObject($this->getStdResponse());
            return $response;
        }

        public function setField($name, $value) {
            if (is_scalar($value) || $value instanceof La_Rpc_Serializable) {
                $this->fields->setField($name, $value);
            } else {
                throw new La_Exception("Not supported value");
            }
        }

        public function setFields(La_Data_IndexedRecordSet $fields) {
            $this->fields->loadFieldsFromArray($fields->toArray());
        }
    }

} //end La_Rpc_FormRequest

if (!class_exists('La_Rpc_RecordSetRequest', false)) {
    class La_Rpc_RecordSetRequest extends La_Rpc_Request {

        /**
         * @return La_Data_IndexedRecordSet
         */
        public function getIndexedRecordSet($key) {
            $response = new La_Data_IndexedRecordSet($key);
            $response->loadFromObject($this->getStdResponse());
            return $response;
        }


        /**
         * @return La_Data_RecordSet
         */
        public function getRecordSet() {
            $response = new La_Data_RecordSet();
            $response->loadFromObject($this->getStdResponse());
            return $response;
        }
    }


} //end La_Rpc_RecordSetRequest

if (!class_exists('La_Rpc_DataRequest', false)) {
    class La_Rpc_DataRequest extends La_Rpc_Request {
        /**
         * @var La_Rpc_Data
         */
        private $data;

        private $filters = array();

        public function __construct($className, $methodName, La_Api_Session $apiSessionObject = null) {
            parent::__construct($className, $methodName, $apiSessionObject);
            $this->data = new La_Rpc_Data();
        }

        /**
         * @return La_Rpc_Data
         */
        public function getData() {
            $response = new La_Rpc_Data();
            $response->loadFromObject($this->getStdResponse());
            return $response;
        }

        public function setField($name, $value) {
            if (is_scalar($value) || $value instanceof La_Rpc_Serializable) {
                $this->data->setParam($name, $value);
            } else {
                throw new La_Exception("Not supported value");
            }
        }

        /**
         * adds filter to grid
         *
         * @param unknown_type $code
         * @param unknown_type $operator
         * @param unknown_type $value
         */
        public function addFilter($code, $operator, $value) {
            $this->filters[] = new La_Data_Filter($code, $operator, $value);
        }

        public function send() {
            $this->addParam('data', $this->data->getParams());

            if(count($this->filters) > 0) {
                $this->addParam("filters", $this->addFiltersParameter());
            }
            parent::send();
        }

        private function addFiltersParameter() {
            $filters = new La_Rpc_Array();

            foreach($this->filters as $filter) {
                $filters->add($filter);
            }

            return $filters;
        }
    }

} //end La_Rpc_DataRequest

if (!class_exists('La_Rpc_Data', false)) {
    class La_Rpc_Data extends La_Object implements La_Rpc_Serializable {
        const NAME  = "name";
        const VALUE = "value";
        const DATA = "data";
        const ID = "id";

        /**
         * @var La_Data_IndexedRecordSet
         */
        private $params;

        /**
         * @var string
         */
        private $id;


        /**
         * @var La_Rpc_FilterCollection
         */
        private $filters;

        /**
         * @var La_Data_IndexedRecordSet
         */
        private $response;

        /**
         *
         * @return La_Data_IndexedRecordSet
         */
        public function getParams() {
            return $this->params;
        }

        /**
         * Create instance to handle DataRequest
         *
         * @param La_Rpc_Params $params
         */
        public function __construct(La_Rpc_Params $params = null) {
            if($params === null) {
                $params = new La_Rpc_Params();
            }

            $this->filters = new La_Rpc_FilterCollection($params);

            $this->params = new La_Data_IndexedRecordSet(self::NAME);
            $this->params->setHeader(array(self::NAME, self::VALUE));

            if ($params->exists(self::DATA) !== null) {
                $this->loadParamsFromArray($params->get(self::DATA));
            }

            $this->id = $params->get(self::ID);

            $this->response = new La_Data_IndexedRecordSet(self::NAME);
            $this->response->setHeader(array(self::NAME, self::VALUE));
        }

        public function addValues(array $values) {
            foreach ($values as $key => $value) {
                $this->setValue($key, $value);
            }
        }

        /**
         * Return id
         *
         * @return string
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Return parameter value
         *
         * @param String $name
         * @return unknown
         */
        public function getParam($name) {
            try {
                return $this->params->getRecord($name)->get(self::VALUE);
            } catch (La_Data_RecordSetNoRowException $e) {
                return null;
            }
        }

        public function setParam($name, $value) {
            self::setValueToRecordset($this->params, $name, $value);
        }

        public function loadFromObject(array $object) {
            $this->response->loadFromObject($object);
            $this->params->loadFromObject($object);
        }

        /**
         * @return La_Rpc_FilterCollection
         */
        public function getFilters() {
            return $this->filters;
        }

        private static function setValueToRecordset(La_Data_IndexedRecordSet $recordset, $name, $value) {
            try {
                $record = $recordset->getRecord($name);
            } catch (La_Data_RecordSetNoRowException $e) {
                $record = $recordset->createRecord();
                $record->set(self::NAME, $name);
                $recordset->addRecord($record);
            }
            $record->set(self::VALUE, $value);
        }

        public function setValue($name, $value) {
            self::setValueToRecordset($this->response, $name, $value);
        }

        public function getSize() {
            return $this->response->getSize();
        }

        public function getValue($name) {
            return $this->response->getRecord($name)->get(self::VALUE);
        }

        public function toObject() {
            return $this->response->toObject();
        }

        public function toText() {
            return $this->response->toText();
        }

        private function loadParamsFromArray($data) {
            for ($i = 1; $i < count($data); $i++) {
                $this->params->add($data[$i]);
            }
        }
    }

} //end La_Rpc_Data

if (!class_exists('La_Rpc_FilterCollection', false)) {
    class La_Rpc_FilterCollection extends La_Object implements IteratorAggregate {

        /**
         * @var array of La_Filter
         */
        private $filters;

        public function __construct(La_Rpc_Params $params = null) {
            $this->filters = array();
            if ($params != null) {
                $this->init($params);
            }
        }

        /**
         * @return La_Rpc_FilterCollection
         */
        public static function  fromJson($json){
            $instance = new La_Rpc_FilterCollection();
            $filters = La_Rpc_Json::decodeStatic($json);
            foreach ($filters as $filter){
                $instance->add($filter);
            }
            return $instance;
        }

        public function add(array $filterArray) {
            $this->filters[] = new La_Filter($filterArray);
        }

        private function init(La_Rpc_Params $params) {
            $filtersArray = $params->get("filters");
            if (!is_array($filtersArray)) {
                return;
            }
            foreach ($filtersArray as $filterArray) {
                $this->add($filterArray);
            }
        }

        /**
         *
         * @return ArrayIterator
         */
        public function getIterator() {
            return new ArrayIterator($this->filters);
        }

        public function addTo(La_SqlBuilder_WhereClause $whereClause) {
            foreach ($this->filters as $filter) {
                $filter->addTo($whereClause);
            }
        }

        public function addSelectedFilterTo(La_SqlBuilder_WhereClause $whereClause, $filterCode, $columnCode = null) {
            if ($columnCode == null) {
                $columnCode = $filterCode;
            }
            foreach ($this->filters as $filter) {
                if ($filter->getCode() == $filterCode) {
                    $oldCode = $filter->getCode();
                    $filter->setCode($columnCode);
                    $filter->addTo($whereClause);
                    $filter->setCode($oldCode);
                }
            }
        }

        /**
         * Returns first filter with specified code.
         * If filter with specified code does not exists null is returned.
         *
         * @param string $code
         * @return array<La_Filter>
         */
        public function getFilter($code) {
            $filters = array();
            foreach ($this->filters as $filter) {
                if ($filter->getCode() == $code) {
                    $filters[] = $filter;
                }
            }
            return $filters;
        }

        public function isFilter($code) {
            foreach ($this->filters as $filter) {
                if ($filter->getCode() == $code) {
                    return true;
                }
            }
            return false;
        }

        public function getFilterValue($code) {
            $filters = $this->getFilter($code);
            if (count($filters) == 1) {
                return $filters[0]->getValue();
            }
            return "";
        }

        public function matches(La_Data_Record $row) {
            foreach ($this->filters as $filter) {
                if (!$filter->matches($row)) {
                    return false;
                }
            }
            return true;
        }

        public function getSize() {
            return count($this->filters);
        }
    }

} //end La_Rpc_FilterCollection

if (!class_exists('La_Php', false)) {
    class La_Php {

        /**
         * Check if function is enabled and exists in php
         *
         * @param $functionName
         * @return boolean Returns true if function exists and is enabled
         */
        public static function isFunctionEnabled($functionName) {
            if (function_exists($functionName) && strstr(ini_get("disable_functions"), $functionName) === false) {
                return true;
            }
            return false;
        }

        /**
         * Check if extension is loaded
         *
         * @param $extensionName
         * @return boolean Returns true if extension is loaded
         */
        public static function isExtensionLoaded($extensionName) {
            return extension_loaded($extensionName);
        }

    }

} //end La_Php

if (!class_exists('La_Rpc_ActionRequest', false)) {
    class La_Rpc_ActionRequest extends La_Rpc_Request {

        /**
         * @return La_Rpc_Action
         */
        public function getAction() {
            $action = new La_Rpc_Action(new La_Rpc_Params());
            $action->loadFromObject($this->getStdResponse());
            return $action;
        }
    }


} //end La_Rpc_ActionRequest

if (!class_exists('La_Rpc_Action', false)) {
    class La_Rpc_Action extends La_Object implements La_Rpc_Serializable {
        private $errorMessage = "";
        private $infoMessage = "";
        private $successCount = 0;
        private $errorCount = 0;
        /**
         * @var La_Rpc_Params
         */
        private $params;

        public function __construct(La_Rpc_Params $params, $infoMessage = '', $errorMessage = '') {
            $this->params = $params;
            $this->infoMessage = $infoMessage;
            $this->errorMessage = $errorMessage;
        }

        /**
         * @return Iterator
         */
        public function getIds() {
            $massHandler = new La_Rpc_MassHandler($this->params);
            return $massHandler->getIds();
        }

        public function getParam($name) {
            return $this->params->get($name);
        }

        public function existsParam($name) {
            return $this->params->exists($name);
        }

        /**
         * Parameter OK is mandatory
         * Parameter I and E is optional and only if there is value it is sent to client (empty values are not transferred)
         *
         * (non-PHPdoc)
         * @see include/Gpf/Rpc/La_Rpc_Serializable#toObject()
         */
        public function toObject() {
            $response = new stdClass();
            $response->S = Gpf::YES;

            if ($this->errorCount > 0) {
                $response->S = Gpf::NO;
                $response->E = $this->_($this->errorMessage, $this->errorCount);
                if (!strlen($response->E)) {
                    unset($response->E);
                }
            }

            if ($this->successCount > 0) {
                $response->I = $this->_($this->infoMessage, $this->successCount);
                if (!strlen($response->I)) {
                    unset($response->I);
                }
            }

            return $response;
        }

        public function loadFromObject(stdClass $object) {
            $this->errorMessage = $object->errorMessage;
            $this->infoMessage = $object->infoMessage;

            if($object->success == Gpf::NO) {
                $this->addError();
            }
        }

        public function isError() {
            return $this->errorCount > 0;
        }

        public function toText() {
            if ($this->isError()) {
                return $this->_($this->errorMessage, $this->errorCount);
            } else {
                return $this->_($this->infoMessage, $this->successCount);
            }
        }

        public function setErrorMessage($message) {
            $this->errorMessage = $message;
        }

        public function getErrorMessage() {
            return $this->errorMessage;
        }

        public function setInfoMessage($message) {
            $this->infoMessage = $message;
        }

        public function addOk() {
            $this->successCount++;
        }

        public function addError() {
            $this->errorCount++;
        }

    }


} //end La_Rpc_Action

if (!class_exists('La_Rpc_Map', false)) {
    class La_Rpc_Map extends La_Object implements La_Rpc_Serializable {

        function __construct(array  $array){
            $this->array = $array;
        }

        public function toObject() {
            return $this->array;
        }

        public function toText() {
            return var_dump($this->array);
        }
    }


} //end La_Rpc_Map

if (!class_exists('La_Log', false)) {
    class La_Log  {
        const CRITICAL = 50;
        const ERROR = 40;
        const WARNING = 30;
        const INFO = 20;
        const DEBUG = 10;

        /**
         * @var La_Log_Logger
         */
        private static $logger;
        	
        /**
         * @return La_Log_Logger
         */
        private static function getLogger() {
            if (self::$logger == null) {
                self::$logger = La_Log_Logger::getInstance();
            }
            return self::$logger;
        }

        private function __construct() {
        }

        public static function disableType($type) {
            self::getLogger()->disableType($type);
        }

        public static function enableAllTypes() {
            self::getLogger()->enableAllTypes();
        }

        /**
         * logs message
         *
         * @param string $message
         * @param string $logLevel
         * @param string $logGroup
         */
        public static function log($message, $logLevel, $logGroup = null) {
            self::getLogger()->log($message, $logLevel, $logGroup);
        }

        /**
         * logs debug message
         *
         * @param string $message
         * @param string $logGroup
         */
        public static function debug($message, $logGroup = null) {
            self::getLogger()->debug($message, $logGroup);
        }

        /**
         * logs info message
         *
         * @param string $message
         * @param string $logGroup
         */
        public static function info($message, $logGroup = null) {
            self::getLogger()->info($message, $logGroup);
        }

        /**
         * logs warning message
         *
         * @param string $message
         * @param string $logGroup
         */
        public static function warning($message, $logGroup = null) {
            self::getLogger()->warning($message, $logGroup);
        }

        /**
         * logs error message
         *
         * @param string $message
         * @param string $logGroup
         */
        public static function error($message, $logGroup = null) {
            self::getLogger()->error($message, $logGroup);
        }

        /**
         * logs critical error message
         *
         * @param string $message
         * @param string $logGroup
         */
        public static function critical($message, $logGroup = null) {
            self::getLogger()->critical($message, $logGroup);
        }

        /**
         * Attach new log system
         *
         * @param string $type
         *      La_Log_LoggerDisplay::TYPE
         *      La_Log_LoggerFile::TYPE
         *      La_Log_LoggerDatabase::TYPE
         * @param string $logLevel
         *      La_Log::CRITICAL
         *      La_Log::ERROR
         *      La_Log::WARNING
         *      La_Log::INFO
         *      La_Log::DEBUG
         * @return La_Log_LoggerBase
         */
        public static function addLogger($type, $logLevel) {
            if($type instanceof La_Log_LoggerBase) {
                return self::getLogger()->addLogger($type, $logLevel);
            }
            return self::getLogger()->add($type, $logLevel);
        }

        public static function removeAll() {
            self::getLogger()->removeAll();
        }
    }

} //end La_Log

if (!class_exists('La_Log_Logger', false)) {
    class La_Log_Logger extends La_Object {
        /**
         * @var array
         */
        static private $instances = array();
        /**
         * @var array
         */
        private $loggers = array();

        /**
         * array of custom parameters
         */
        private $customParameters = array();

        private $disabledTypes = array();

        private $group = null;
        private $type = null;
        private $logToDisplay = false;

        /**
         * returns instance of logger class.
         * You can add instance name, if you want to have multiple independent instances of logger
         *
         * @param string $instanceName
         * @return La_Log_Logger
         */
        public static function getInstance($instanceName = '_') {
            if($instanceName == '') {
                $instanceName = '_';
            }

            if (!array_key_exists($instanceName, self::$instances)) {
                self::$instances[$instanceName] = new La_Log_Logger();
            }
            $instance = self::$instances[$instanceName];
            return $instance;
        }

        public static function isLoggerInsert($sqlString) {
            return strpos($sqlString, 'INSERT INTO ' . La_Db_Table_Logs::getName()) !== false;
        }

        /**
         * attachs new log system
         *
         * @param unknown_type $system
         * @return La_Log_LoggerBase
         */
        public function add($type, $logLevel) {
            if($type == La_Log_LoggerDisplay::TYPE) {
                $this->logToDisplay = true;
            }
            return $this->addLogger($this->create($type), $logLevel);
        }

        /**
         * Checks if logger with te specified type was already initialized
         *
         * @param unknown_type $type
         * @return unknown
         */
        public function checkLoggerTypeExists($type) {
            if(array_key_exists($type, $this->loggers)) {
                return true;
            }

            return false;
        }

        /**
         * returns true if debugging writes log to display
         *
         * @return boolean
         */
        public function isLogToDisplay() {
            return $this->logToDisplay;
        }

        public function removeAll() {
            $this->loggers = array();
            $this->customParameters = array();
            $this->disabledTypes = array();
            $this->group = null;
        }

        /**
         *
         * @param La_Log_LoggerBase $logger
         * @param int $logLevel
         * @return La_Log_LoggerBase
         */
        public function addLogger(La_Log_LoggerBase $logger, $logLevel) {
            if(!$this->checkLoggerTypeExists($logger->getType())) {
                $logger->setLogLevel($logLevel);
                $this->loggers[$logger->getType()] = $logger;
                return $logger;
            } else {
                $ll = new La_Log_LoggerDatabase();
                $existingLogger = $this->loggers[$logger->getType()];
                if($existingLogger->getLogLevel() > $logLevel) {
                    $existingLogger->setLogLevel($logLevel);
                }
                return $existingLogger;
            }
        }

        public function getGroup() {
            return $this->group;
        }

        public function setGroup($group = null) {
            $this->group = $group;
            if($group === null) {
                $this->group = La_Common_String::generateId(10);
            }
        }

        public function setType($type) {
            $this->type = $type;
        }

        /**
         * function sets custom parameter for the logger
         *
         * @param string $name
         * @param string $value
         */
        public function setCustomParameter($name, $value) {
            $this->customParameters[$name] = $value;
        }

        /**
         * returns custom parameter
         *
         * @param string $name
         * @return string
         */
        public function getCustomParameter($name) {
            if(isset($this->customParameters[$name])) {
                return $this->customParameters[$name];
            }
            return '';
        }

        /**
         * logs message
         *
         * @param string $message
         * @param string $logLevel
         * @param string $logGroup
         */
        public function log($message, $logLevel, $logGroup = null) {
            $time = time();
            $backArr = debug_backtrace();
            $group = $logGroup;
            if($this->group !== null) {
                $group = $this->group;
                if($logGroup !== null) {
                    $group .= ' ' . $logGroup;
                }
            }

            $callingFile = $this->findLogFile();
            $file = $callingFile['file'];
            if(isset($callingFile['classVariables'])) {
                $file .= ' '.$callingFile['classVariables'];
            }
            $line = $callingFile['line'];

            $ip = La_Http::getRemoteIp();
            if ($ip = '') {
                $ip = '127.0.0.1';
            }

            foreach ($this->loggers as $logger) {
                if(!in_array($logger->getType(), $this->disabledTypes)) {
                    $logger->logMessage($time, $message, $logLevel, $group, $ip, $file, $line, $this->type);
                }
            }
        }

        /**
         * logs debug message
         *
         * @param string $message
         * @param string $logGroup
         */
        public function debug($message, $logGroup = null) {
            $this->log($message, La_Log::DEBUG, $logGroup);
        }

        /**
         * logs info message
         *
         * @param string $message
         * @param string $logGroup
         */
        public function info($message, $logGroup = null) {
            $this->log($message, La_Log::INFO, $logGroup);
        }

        /**
         * logs warning message
         *
         * @param string $message
         * @param string $logGroup
         */
        public function warning($message, $logGroup = null) {
            $this->log($message, La_Log::WARNING, $logGroup);
        }

        /**
         * logs error message
         *
         * @param string $message
         * @param string $logGroup
         */
        public function error($message, $logGroup = null) {
            $this->log($message, La_Log::ERROR, $logGroup);
        }

        /**
         * logs critical error message
         *
         * @param string $message
         * @param string $logGroup
         */
        public function critical($message, $logGroup = null) {
            $this->log($message, La_Log::CRITICAL, $logGroup);
        }

        public function disableType($type) {
            $this->disabledTypes[$type] =$type;
        }

        public function enableAllTypes() {
            $this->disabledTypes = array();
        }

        /**
         *
         * @return La_Log_LoggerBase
         */
        private function create($type) {
            switch($type) {
                case La_Log_LoggerDisplay::TYPE:
                    return new La_Log_LoggerDisplay();
                case La_Log_LoggerFile::TYPE:
                    return new La_Log_LoggerFile();
                case La_Log_LoggerDatabase::TYPE:
                case 'db':
                    return new La_Log_LoggerDatabase();
            }
            throw new La_Log_Exception("Log system '$type' does not exist");
        }

        private function findLogFile() {
            $calls = debug_backtrace();

            $foundObject = null;

            // special handling for sql benchmarks
            if($this->sqlBenchmarkFound($calls)) {
                $foundObject = $this->findFileBySqlBenchmark();
            }

            if($foundObject == null) {
                $foundObject = $this->findFileByCallingMethod($calls);
            }
            if($foundObject == null) {
                $foundObject = $this->findLatestObjectBeforeString("Logger.class.php");
            }
            if($foundObject == null) {
                $last = count($calls);
                $last -= 1;
                if($last <0) {
                    $last = 0;
                }

                $foundObject = $calls[$last];
            }

            return $foundObject;
        }

        private function sqlBenchmarkFound($calls) {
            foreach($calls as $obj) {
                if(isset($obj['function']) && $obj['function'] == "sqlBenchmarkEnd") {
                    return true;
                }
            }
            return false;
        }

        private function findFileBySqlBenchmark() {
            $foundFile = $this->findLatestObjectBeforeString("DbEngine");
            if($foundFile != null && is_object($foundFile['object'])) {
                $foundFile['classVariables'] = $this->getObjectVariables($foundFile['object']);
            }
            return $foundFile;
        }

        private function getObjectVariables($object) {
            if(is_object($object)) {
                $class = get_class($object);
                $methods = get_class_methods($class);
                if(in_array("__toString", $methods)) {
                    return $object->__toString();
                }
            }
            return '';
        }

        private function findFileByCallingMethod($calls) {
            $functionNames = array('debug', 'info', 'warning', 'error', 'critical', 'log');
            $foundObject = null;
            foreach($functionNames as $name) {
                $foundObject = $this->findCallingFile($calls, $name);
                if($foundObject != null) {
                    return $foundObject;
                }
            }

            return null;
        }

        private function findCallingFile($calls, $functionName) {
            foreach($calls as $obj) {
                if(isset($obj['function']) && $obj['function'] == $functionName) {
                    return $obj;
                }
            }

            return null;
        }

        private function findLatestObjectBeforeString($text) {
            $callsReversed = array_reverse( debug_backtrace() );

            $lastObject = null;
            foreach($callsReversed as $obj) {
                if(!isset($obj['file'])) {
                    continue;
                }
                $pos = strpos($obj['file'], $text);
                if($pos !== false && $lastObject != null) {
                    return $lastObject;
                }
                $lastObject = $obj;
            }
            return null;
        }
    }

} //end La_Log_Logger

if (!class_exists('La_Api_IncompatibleVersionException', false)) {
    class La_Api_IncompatibleVersionException extends Exception {

        private $apiLink;

        public function __construct($url) {
            $this->apiLink = $url. '?C=La_Api_DownloadAPI&M=download&FormRequest=Y&FormResponse=Y';
            parent::__construct('Version of API not corresponds to the Application version. Please <a href="' . $this->apiLink . '">download latest version of API</a>.', 0);
        }

        public function getApiDownloadLink() {
            return $this->apiLink;
        }

    }

} //end La_Api_IncompatibleVersionException

if (!class_exists('La_Api_Session', false)) {
    class La_Api_Session extends La_Object {
        const MERCHANT = 'M';
        const AFFILIATE = 'A';

        private $url;
        private $sessionId = '';
        private $debug = false;
        private $message = '';
        private $roleType = '';

        public function __construct($url) {
            $this->url = $url;
        }
        /**
         *
         * @param $username
         * @param $password
         * @param $roleType La_Api_Session::MERCHANT or La_Api_Session::AFFILIATE
         * @param $languageCode language code (e.g. en-US, de-DE, sk, cz, du, ...)
         * @return boolean true if user was sucesfully logged
         * @throws La_Api_IncompatibleVersionException
         */
        public function login($username, $password, $roleType = self::MERCHANT, $languageCode = null) {
            $request = new La_Rpc_FormRequest("La_Api_AuthService", "authenticate");
            $request->setUrl($this->url);
            $request->setField("username", $username);
            $request->setField("password", $password);
            $request->setField("roleType", $roleType);
            $request->setField('apiVersion', self::getAPIVersion());
            if($languageCode != null) {
                $request->setField("language", $languageCode);
            }

            $this->roleType = $roleType;

            try {
                $request->sendNow();
            } catch(Exception $e) {
                $this->setMessage("Connection error: ".$e->getMessage());
                return false;
            }

            $form = $request->getForm();
            $this->checkApiVersion($form);

            $this->message = $form->getInfoMessage();

            if($form->isSuccessful() && $form->existsField("S")) {
                $this->sessionId = $form->getFieldValue("S");
                $this->setMessage($form->getInfoMessage());
                return true;
            }

            $this->setMessage($form->getErrorMessage());
            return false;
        }

        /**
         * Get version of installed application
         *
         * @return string version of installed application
         */
        public function getAppVersion() {
            $request = new La_Rpc_FormRequest("La_Api_AuthService", "getAppVersion");
            $request->setUrl($this->url);

            try {
                $request->sendNow();
            } catch(Exception $e) {
                $this->setMessage("Connection error: ".$e->getMessage());
                return false;
            }

            $form = $request->getForm();
            return $form->getFieldValue('version');
        }


        public function getMessage() {
            return $this->message;
        }

        private function setMessage($msg) {
            $this->message = $msg;
        }

        public function getDebug() {
            return $this->debug;
        }

        public function setDebug($debug = true) {
            $this->debug = $debug;
        }

        public function getSessionId() {
            return $this->sessionId;
        }
        	
        public function setSessionId($id) {
            $this->sessionId = $id;
        }

        public function getRoleType() {
            return $this->roleType;
        }

        public function getUrl() {
            return $this->url;
        }

        public function getUrlWithSessionInfo($url) {
            if (strpos($url, '?') === false) {
                return $url . '?S=' . $this->getSessionId();
            }
            return $url . '&S=' . $this->getSessionId();
        }

        /**
         * @param $latestVersion
         * @throws La_Api_IncompatibleVersionException
         */
        private function checkApiVersion(La_Rpc_Form $form) {
            if ($form->getFieldValue('correspondsApi') === Gpf::NO) {
                throw new La_Api_IncompatibleVersionException($this->url);
            }
        }

        /**
         * @return String
         */
        public static function getAPIVersion($fileName = __FILE__) {
            $fileHandler = fopen($fileName, 'r');
            fseek($fileHandler, -6 -32, SEEK_END);
            $hash = fgets($fileHandler);
            return substr($hash, 0, -1);
        }
    }

} //end La_Api_Session

if (!class_exists('La_Rpc_Json', false)) {
    class La_Rpc_Json implements La_Rpc_DataEncoder, La_Rpc_DataDecoder {
        /**
         * Marker constant for Services_JSON::decode(), used to flag stack state
         */
        const SERVICES_JSON_SLICE = 1;

        /**
         * Marker constant for Services_JSON::decode(), used to flag stack state
         */
        const SERVICES_JSON_IN_STR = 2;

        /**
         * Marker constant for Services_JSON::decode(), used to flag stack state
         */
        const SERVICES_JSON_IN_ARR = 3;

        /**
         * Marker constant for Services_JSON::decode(), used to flag stack state
         */
        const SERVICES_JSON_IN_OBJ = 4;

        /**
         * Marker constant for Services_JSON::decode(), used to flag stack state
         */
        const SERVICES_JSON_IN_CMT = 5;

        /**
         * Behavior switch for Services_JSON::decode()
         */
        const SERVICES_JSON_LOOSE_TYPE = 16;

        /**
         * Behavior switch for Services_JSON::decode()
         */
        const SERVICES_JSON_SUPPRESS_ERRORS = 32;

        /**
         * constructs a new JSON instance
         *
         * @param    int     $use    object behavior flags; combine with boolean-OR
         *
         *                           possible values:
         *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
         *                                   "{...}" syntax creates associative arrays
         *                                   instead of objects in decode().
         *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
         *                                   Values which can't be encoded (e.g. resources)
         *                                   appear as NULL instead of throwing errors.
         *                                   By default, a deeply-nested resource will
         *                                   bubble up with an error, so all return values
         *                                   from encode() should be checked with isError()
         */
        function __construct($use = 0)
        {
            $this->use = $use;
        }

        /**
         * @var La_Rpc_Json
         */
        private static $instance;

        /**
         *
         * @return La_Rpc_Json
         */
        private function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        public static function encodeStatic($var) {
            return self::getInstance()->encode($var);
        }

        public static function decodeStatic($var) {
            return self::getInstance()->decode($var);
        }

        /**
         * convert a string from one UTF-16 char to one UTF-8 char
         *
         * Normally should be handled by mb_convert_encoding, but
         * provides a slower PHP-only method for installations
         * that lack the multibye string extension.
         *
         * @param    string  $utf16  UTF-16 character
         * @return   string  UTF-8 character
         * @access   private
         */
        function utf162utf8($utf16)
        {
            // oh please oh please oh please oh please oh please
            if(La_Php::isFunctionEnabled('mb_convert_encoding')) {
                return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
            }

            $bytes = (ord($utf16{0}) << 8) | ord($utf16{1});

            switch(true) {
                case ((0x7F & $bytes) == $bytes):
                    // this case should never be reached, because we are in ASCII range
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0x7F & $bytes);

                case (0x07FF & $bytes) == $bytes:
                    // return a 2-byte UTF-8 character
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0xC0 | (($bytes >> 6) & 0x1F))
                    . chr(0x80 | ($bytes & 0x3F));

                case (0xFFFF & $bytes) == $bytes:
                    // return a 3-byte UTF-8 character
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0xE0 | (($bytes >> 12) & 0x0F))
                    . chr(0x80 | (($bytes >> 6) & 0x3F))
                    . chr(0x80 | ($bytes & 0x3F));
            }

            // ignoring UTF-32 for now, sorry
            return '';
        }

        /**
         * convert a string from one UTF-8 char to one UTF-16 char
         *
         * Normally should be handled by mb_convert_encoding, but
         * provides a slower PHP-only method for installations
         * that lack the multibye string extension.
         *
         * @param    string  $utf8   UTF-8 character
         * @return   string  UTF-16 character
         * @access   private
         */
        function utf82utf16($utf8)
        {
            // oh please oh please oh please oh please oh please
            if(La_Php::isFunctionEnabled('mb_convert_encoding')) {
                return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
            }

            switch(strlen($utf8)) {
                case 1:
                    // this case should never be reached, because we are in ASCII range
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return $utf8;

                case 2:
                    // return a UTF-16 character from a 2-byte UTF-8 char
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0x07 & (ord($utf8{0}) >> 2))
                    . chr((0xC0 & (ord($utf8{0}) << 6))
                    | (0x3F & ord($utf8{1})));

                case 3:
                    // return a UTF-16 character from a 3-byte UTF-8 char
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr((0xF0 & (ord($utf8{0}) << 4))
                    | (0x0F & (ord($utf8{1}) >> 2)))
                    . chr((0xC0 & (ord($utf8{1}) << 6))
                    | (0x7F & ord($utf8{2})));
            }

            // ignoring UTF-32 for now, sorry
            return '';
        }

        public function encodeResponse(La_Rpc_Serializable $response) {
            return $this->encode($response->toObject());
        }

        /**
         * encodes an arbitrary variable into JSON format
         *
         * @param    mixed   $var    any number, boolean, string, array, or object to be encoded.
         *                           see argument 1 to Services_JSON() above for array-parsing behavior.
         *                           if var is a strng, note that encode() always expects it
         *                           to be in ASCII or UTF-8 format!
         *
         * @return   mixed   JSON string representation of input var or an error if a problem occurs
         * @access   public
         */
        public function encode($var) {
            if ($this->isJsonEncodeEnabled()) {
                return @json_encode($var);
            }
            switch (gettype($var)) {
                case 'boolean':
                    return $var ? 'true' : 'false';

                case 'NULL':
                    return 'null';

                case 'integer':
                    return (int) $var;

                case 'double':
                case 'float':
                    return (float) $var;

                case 'string':
                    // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                    $ascii = '';
                    $strlen_var = strlen($var);

                    /*
                     * Iterate over every character in the string,
                     * escaping with a slash or encoding to UTF-8 where necessary
                     */
                    for ($c = 0; $c < $strlen_var; ++$c) {

                        $ord_var_c = ord($var{$c});

                        switch (true) {
                            case $ord_var_c == 0x08:
                                $ascii .= '\b';
                                break;
                            case $ord_var_c == 0x09:
                                $ascii .= '\t';
                                break;
                            case $ord_var_c == 0x0A:
                                $ascii .= '\n';
                                break;
                            case $ord_var_c == 0x0C:
                                $ascii .= '\f';
                                break;
                            case $ord_var_c == 0x0D:
                                $ascii .= '\r';
                                break;

                            case $ord_var_c == 0x22:
                            case $ord_var_c == 0x2F:
                            case $ord_var_c == 0x5C:
                                // double quote, slash, slosh
                                $ascii .= '\\'.$var{$c};
                                break;

                            case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                                // characters U-00000000 - U-0000007F (same as ASCII)
                                $ascii .= $var{$c};
                                break;

                            case (($ord_var_c & 0xE0) == 0xC0):
                                // characters U-00000080 - U-000007FF, mask 1 1 0 X X X X X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                                $c += 1;
                                $utf16 = $this->utf82utf16($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xF0) == 0xE0):
                                // characters U-00000800 - U-0000FFFF, mask 1 1 1 0 X X X X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c,
                                ord($var{$c + 1}),
                                ord($var{$c + 2}));
                                $c += 2;
                                $utf16 = $this->utf82utf16($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xF8) == 0xF0):
                                // characters U-00010000 - U-001FFFFF, mask 1 1 1 1 0 X X X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c,
                                ord($var{$c + 1}),
                                ord($var{$c + 2}),
                                ord($var{$c + 3}));
                                $c += 3;
                                $utf16 = $this->utf82utf16($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xFC) == 0xF8):
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c,
                                ord($var{$c + 1}),
                                ord($var{$c + 2}),
                                ord($var{$c + 3}),
                                ord($var{$c + 4}));
                                $c += 4;
                                $utf16 = $this->utf82utf16($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xFE) == 0xFC):
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c,
                                ord($var{$c + 1}),
                                ord($var{$c + 2}),
                                ord($var{$c + 3}),
                                ord($var{$c + 4}),
                                ord($var{$c + 5}));
                                $c += 5;
                                $utf16 = $this->utf82utf16($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;
                        }
                    }

                    return '"'.$ascii.'"';

                case 'array':
                    /*
                     * As per JSON spec if any array key is not an integer
                     * we must treat the the whole array as an object. We
                     * also try to catch a sparsely populated associative
                     * array with numeric keys here because some JS engines
                     * will create an array with empty indexes up to
                     * max_index which can cause memory issues and because
                     * the keys, which may be relevant, will be remapped
                     * otherwise.
                     *
                     * As per the ECMA and JSON specification an object may
                     * have any string as a property. Unfortunately due to
                     * a hole in the ECMA specification if the key is a
                     * ECMA reserved word or starts with a digit the
                     * parameter is only accessible using ECMAScript's
                     * bracket notation.
                     */

                    // treat as a JSON object
                    if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                        $properties = array_map(array($this, 'name_value'), array_keys($var), array_values($var));

                        foreach($properties as $property) {
                            if(La_Rpc_Json::isError($property)) {
                                return $property;
                            }
                        }

                        return '{' . join(',', $properties) . '}';
                    }

                    // treat it like a regular array
                    $elements = array_map(array($this, 'encode'), $var);

                    foreach($elements as $element) {
                        if(La_Rpc_Json::isError($element)) {
                            return $element;
                        }
                    }

                    return '[' . join(',', $elements) . ']';

                case 'object':
                    $vars = get_object_vars($var);

                    $properties = array_map(array($this, 'name_value'),
                    array_keys($vars),
                    array_values($vars));

                    foreach($properties as $property) {
                        if(La_Rpc_Json::isError($property)) {
                            return $property;
                        }
                    }

                    return '{' . join(',', $properties) . '}';

                default:
                    if ($this->use & self::SERVICES_JSON_SUPPRESS_ERRORS) {
                        return 'null';
                    }
                    return new La_Rpc_Json_Error(gettype($var)." can not be encoded as JSON string");
            }
        }

        /**
         * array-walking function for use in generating JSON-formatted name-value pairs
         *
         * @param    string  $name   name of key to use
         * @param    mixed   $value  reference to an array element to be encoded
         *
         * @return   string  JSON-formatted name-value pair, like '"name":value'
         * @access   private
         */
        function name_value($name, $value)
        {
            $encoded_value = $this->encode($value);

            if(La_Rpc_Json::isError($encoded_value)) {
                return $encoded_value;
            }

            return $this->encode(strval($name)) . ':' . $encoded_value;
        }

        /**
         * reduce a string by removing leading and trailing comments and whitespace
         *
         * @param    $str    string      string value to strip of comments and whitespace
         *
         * @return   string  string value stripped of comments and whitespace
         * @access   private
         */
        function reduce_string($str)
        {
            $str = preg_replace(array(

            // eliminate single line comments in '// ...' form
                  '#^\s*//(.+)$#m',

            // eliminate multi-line comments in '/* ... */' form, at start of string
                  '#^\s*/\*(.+)\*/#Us',

            // eliminate multi-line comments in '/* ... */' form, at end of string
                  '#/\*(.+)\*/\s*$#Us'
  
                  ), '', $str);

                  // eliminate extraneous space
                  return trim($str);
        }

        /**
         * decodes a JSON string into appropriate variable
         *
         * @param    string  $str    JSON-formatted string
         *
         * @return   mixed   number, boolean, string, array, or object
         *                   corresponding to given JSON input string.
         *                   See argument 1 to Services_JSON() above for object-output behavior.
         *                   Note that decode() always returns strings
         *                   in ASCII or UTF-8 format!
         * @access   public
         */
        function decode($str)
        {
            if ($this->isJsonDecodeEnabled()) {
                return json_decode($str);
            }

            $str = $this->reduce_string($str);

            switch (strtolower($str)) {
                case 'true':
                    return true;

                case 'false':
                    return false;

                case 'null':
                    return null;

                default:
                    $m = array();

                    if (is_numeric($str)) {
                        // Lookie-loo, it's a number

                        // This would work on its own, but I'm trying to be
                        // good about returning integers where appropriate:
                        // return (float)$str;

                        // Return float or int, as appropriate
                        return ((float)$str == (integer)$str)
                        ? (integer)$str
                        : (float)$str;

                    } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                        // STRINGS RETURNED IN UTF-8 FORMAT
                        $delim = substr($str, 0, 1);
                        $chrs = substr($str, 1, -1);
                        $utf8 = '';
                        $strlen_chrs = strlen($chrs);

                        for ($c = 0; $c < $strlen_chrs; ++$c) {

                            $substr_chrs_c_2 = substr($chrs, $c, 2);
                            $ord_chrs_c = ord($chrs{$c});

                            switch (true) {
                                case $substr_chrs_c_2 == '\b':
                                    $utf8 .= chr(0x08);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\t':
                                    $utf8 .= chr(0x09);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\n':
                                    $utf8 .= chr(0x0A);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\f':
                                    $utf8 .= chr(0x0C);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\r':
                                    $utf8 .= chr(0x0D);
                                    ++$c;
                                    break;

                                case $substr_chrs_c_2 == '\\"':
                                case $substr_chrs_c_2 == '\\\'':
                                case $substr_chrs_c_2 == '\\\\':
                                case $substr_chrs_c_2 == '\\/':
                                    if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                    ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
                                        $utf8 .= $chrs{++$c};
                                    }
                                    break;

                                case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                    // single, escaped unicode character
                                    $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                    . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                    $utf8 .= $this->utf162utf8($utf16);
                                    $c += 5;
                                    break;

                                case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                    $utf8 .= $chrs{$c};
                                    break;

                                case ($ord_chrs_c & 0xE0) == 0xC0:
                                    // characters U-00000080 - U-000007FF, mask 1 1 0 X X X X X
                                    //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 2);
                                    ++$c;
                                    break;

                                case ($ord_chrs_c & 0xF0) == 0xE0:
                                    // characters U-00000800 - U-0000FFFF, mask 1 1 1 0 X X X X
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 3);
                                    $c += 2;
                                    break;

                                case ($ord_chrs_c & 0xF8) == 0xF0:
                                    // characters U-00010000 - U-001FFFFF, mask 1 1 1 1 0 X X X
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 4);
                                    $c += 3;
                                    break;

                                case ($ord_chrs_c & 0xFC) == 0xF8:
                                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 5);
                                    $c += 4;
                                    break;

                                case ($ord_chrs_c & 0xFE) == 0xFC:
                                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 6);
                                    $c += 5;
                                    break;

                            }

                        }

                        return $utf8;

                    } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                        // array, or object notation

                        if ($str{0} == '[') {
                            $stk = array(self::SERVICES_JSON_IN_ARR);
                            $arr = array();
                        } else {
                            if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                                $stk = array(self::SERVICES_JSON_IN_OBJ);
                                $obj = array();
                            } else {
                                $stk = array(self::SERVICES_JSON_IN_OBJ);
                                $obj = new stdClass();
                            }
                        }

                        array_push($stk, array('what'  => self::SERVICES_JSON_SLICE,
                                             'where' => 0,
                                             'delim' => false));

                        $chrs = substr($str, 1, -1);
                        $chrs = $this->reduce_string($chrs);

                        if ($chrs == '') {
                            if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                                return $arr;

                            } else {
                                return $obj;

                            }
                        }

                        //print("\nparsing {$chrs}\n");

                        $strlen_chrs = strlen($chrs);

                        for ($c = 0; $c <= $strlen_chrs; ++$c) {

                            $top = end($stk);
                            $substr_chrs_c_2 = substr($chrs, $c, 2);

                            if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == self::SERVICES_JSON_SLICE))) {
                                // found a comma that is not inside a string, array, etc.,
                                // OR we've reached the end of the character list
                                $slice = substr($chrs, $top['where'], ($c - $top['where']));
                                array_push($stk, array('what' => self::SERVICES_JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
                                //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                                if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                                    // we are in an array, so just push an element onto the stack
                                    array_push($arr, $this->decode($slice));

                                } elseif (reset($stk) == self::SERVICES_JSON_IN_OBJ) {
                                    // we are in an object, so figure
                                    // out the property name and set an
                                    // element in an associative array,
                                    // for now
                                    $parts = array();

                                    if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                        // "name":value pair
                                        $key = $this->decode($parts[1]);
                                        $val = $this->decode($parts[2]);

                                        if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                        // name:value pair, where name is unquoted
                                        $key = $parts[1];
                                        $val = $this->decode($parts[2]);

                                        if ($this->use & self::SERVICES_JSON_LOOSE_TYPE) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    }

                                }

                            } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != self::SERVICES_JSON_IN_STR)) {
                                // found a quote, and we are not inside a string
                                array_push($stk, array('what' => self::SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                                //print("Found start of string at {$c}\n");

                            } elseif (($chrs{$c} == $top['delim']) &&
                            ($top['what'] == self::SERVICES_JSON_IN_STR) &&
                            (($chrs{$c - 1} != '\\') ||
                            ($chrs{$c - 1} == '\\' && $chrs{$c - 2} == '\\'))) {
                                // found a quote, we're in a string, and it's not escaped
                                array_pop($stk);
                                //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");

                            } elseif (($chrs{$c} == '[') &&
                            in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                                // found a left-bracket, and we are in an array, object, or slice
                                array_push($stk, array('what' => self::SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false));
                                //print("Found start of array at {$c}\n");

                            } elseif (($chrs{$c} == ']') && ($top['what'] == self::SERVICES_JSON_IN_ARR)) {
                                // found a right-bracket, and we're in an array
                                array_pop($stk);
                                //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            } elseif (($chrs{$c} == '{') &&
                            in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                                // found a left-brace, and we are in an array, object, or slice
                                array_push($stk, array('what' => self::SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false));
                                //print("Found start of object at {$c}\n");

                            } elseif (($chrs{$c} == '}') && ($top['what'] == self::SERVICES_JSON_IN_OBJ)) {
                                // found a right-brace, and we're in an object
                                array_pop($stk);
                                //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            } elseif (($substr_chrs_c_2 == '/*') &&
                            in_array($top['what'], array(self::SERVICES_JSON_SLICE, self::SERVICES_JSON_IN_ARR, self::SERVICES_JSON_IN_OBJ))) {
                                // found a comment start, and we are in an array, object, or slice
                                array_push($stk, array('what' => self::SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false));
                                $c++;
                                //print("Found start of comment at {$c}\n");

                            } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::SERVICES_JSON_IN_CMT)) {
                                // found a comment end, and we're in one now
                                array_pop($stk);
                                $c++;

                                for ($i = $top['where']; $i <= $c; ++$i)
                                $chrs = substr_replace($chrs, ' ', $i, 1);

                                //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            }

                        }

                        if (reset($stk) == self::SERVICES_JSON_IN_ARR) {
                            return $arr;

                        } elseif (reset($stk) == self::SERVICES_JSON_IN_OBJ) {
                            return $obj;

                        }

                    }
            }
        }

        protected function isJsonEncodeEnabled() {
            return La_Php::isFunctionEnabled('json_encode');
        }

        protected function isJsonDecodeEnabled() {
            return La_Php::isFunctionEnabled('json_decode');
        }


        /**
         * @todo Ultimately, this should just call PEAR::isError()
         */
        function isError($data, $code = null)
        {
            if (is_object($data) &&
            (get_class($data) == 'La_Rpc_Json_Error' || is_subclass_of($data, 'La_Rpc_Json_Error'))) {
                return true;
            }
            return false;
        }
    }

    class La_Rpc_Json_Error {
        private $message;

        public function __construct($message) {
            $this->message = $message;
        }
    }


} //end La_Rpc_Json

if (!class_exists('La_Rpc_JsonObject', false)) {
    class La_Rpc_JsonObject extends La_Object {

        public function __construct($object = null) {
            if ($object != null) {
                $this->initFrom($object);
            }
        }

        public function decode($string) {
            if ($string == null || $string == "") {
                throw new La_Exception("Invalid format (".get_class($this).")");
            }
            $string = stripslashes($string);
            $json = new La_Rpc_Json();
            $object = $json->decode($string);
            if (!is_object($object)) {
                throw new La_Exception("Invalid format (".get_class($this).")");
            }
            $this->initFrom($object);
        }

        private function initFrom($object) {
            $object_vars = get_object_vars($object);
            foreach ($object_vars as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }
        }

        public function encode() {
            $json = new La_Rpc_Json();
            return $json->encode($this);
        }

        public function __toString() {
            return $this->encode();
        }
    }

} //end La_Rpc_JsonObject

if (!class_exists('La_Net_Http_Client', false)) {
    class La_Net_Http_Client extends La_Net_Http_ClientBase {

        protected function isNetworkingEnabled() {
            return Gpf::YES;
        }

    }

} //end La_Net_Http_Client

if (!class_exists('Gpf', false)) {
    class Gpf {
        const YES = 'Y';
        const NO = 'N';
    }
}
/*
 VERSION
 8420bb3c08118181a7f384fda817c252
 */
?>
