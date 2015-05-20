<?php
/**
 * @author winsen
 * @description wechat sdk base class
 * @version 1.0
 */
class Wechat
{
    /*
     * @todo debug mode, true if request to save error message into errors.log
     * @var boolean
     */
    private $debug;
    /*
     * @todo save request data as an array
     * @var array
     */
    private $request;
    /*
     * @todo save custom token
     * @var string
     */
    private $token;

    public function __construct($token, $debug = FALSE)
    {
        $this->token = $token;

        // if url access test
        if($this->isValid())
        {
            exit($_GET['echostr']);
        }

        // validate raw request data if empty
        if(empty($GLOBALS['HTTP_RAW_POST_DATA']))
        {
            exit('data is empty!');
        }

        $this->debug = $debug;
        set_error_handler(array(&$this, 'errorHandler'));

        // receive request and convert into array
        // then change array key into lower case
        $xml =  simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA']);
        $this->request = array_change_key_case($xml, CASE_LOWER);

        // validate request URI if comes from webchat server
        if($this->validateSignature($token))
        {
            exit('Validate signature failed!');
        }
    }

    /*
     * @todo check if this request just for url check test
     * @return boolean
     */
    private function isValid()
    {
      return isset($_GET['echostr']);
    }

    /**
     * @todo return request param's value
     */
    private function getRequest($param = false)
    {
        if(false == $param)
        {
            return $this->request;
        }

        $param = strtolower($param);
        if(isset($this->request[$param]))
        {
            return $this->request[$param];
        }

        return null;
    }

    /**
     * @todo validate signature
     * @param  string $token custom token
     * @return boolean
     */
    private function validateSignature($token)
    {
      if ( ! (isset($_GET['signature']) && isset($_GET['timestamp']) && isset($_GET['nonce']))) {
        return FALSE;
      }
      
      $signature = $_GET['signature'];
      $timestamp = $_GET['timestamp'];
      $nonce = $_GET['nonce'];

      $signatureArray = array($token, $timestamp, $nonce);
      sort($signatureArray);

      return sha1(implode($signatureArray)) == $signature;
    }

    /*
     * @todo custom error handler function
     * @param  int $level   error code
     * @param  string $msg  error content
     * @param  string $file error file
     * @param  int $line    error line number
     * @return void
     */
    protected function errorHandler($level, $msg, $file, $line) {
      if ( ! $this->debug) {
        return;
      }

      $error_type = array(
        // E_ERROR             => 'Error',
        E_WARNING           => 'Warning',
        // E_PARSE             => 'Parse Error',
        E_NOTICE            => 'Notice',
        // E_CORE_ERROR        => 'Core Error',
        // E_CORE_WARNING      => 'Core Warning',
        // E_COMPILE_ERROR     => 'Compile Error',
        // E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
      );

      $template = '%s: %s File: %s Line: %s';
      exit(sprintf($template, $error_type[$level], $msg, $file, $line));
    }

    public function run()
    {
        switch($this->getRequest('msgType'))
        {
            // event handler
            case 'event':
                break;
            // text handler
            case 'text':
                break;
            // image handler
            case 'image':
                break;
            // voice handler
            case 'voice':
                break;
            // video handler
            case 'video':
                break;
            // location handler
            case 'location':
                break;
            // link handler
            case 'link':
                break;
            default:
                break;
        }
    }
}
