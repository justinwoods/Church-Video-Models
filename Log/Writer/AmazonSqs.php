<?php

class JW_Log_Writer_AmazonSqs extends Zend_Log_Writer_Abstract
{

    private $_sqs;
    private $_queue_url;
    
    private $_events = array();
    
    public function __construct($sqs, $queue_name)
    {
        $this->_sqs = $sqs;
        $this->_queue_url = $this->_sqs->safeCreate($queue_name);
    }
    
    public function _write($event)
    {
        $this->_events[] = $event;
    }
    
    public function shutdown()
    {
        if(empty($this->_events)) {
            return;
        }
        
        foreach($this->_events as $event) {
            $this->_sqs->log($event);
        }
    }
    
    public static function factory($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        
        if (!is_array($config)) {
            throw new Exception('factory expects an array or Zend_Config instance');
        }
        
        $default = array(
            'sqs' => 		new JW_Service_Amazon_Sqs,
            'queue_name' => 	null
        );
        
        $config = array_merge($default, $config);
        
        return new self(
            $config['sqs'],
            $config['queue_name']
        );
        
    }
    
}