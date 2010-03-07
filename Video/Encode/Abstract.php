<?php

class JW_Video_Encode_Abstract
{

    private $_config		= null;
    private $_input_file	= null;
    private $_output_file	= null;
    private $_monitor_file	= null;
    
    protected $_encoder_settings = null;
        
    public function __construct($config)
    {
        $this->setConfig($config);
    }
    
    protected function _getCommand()
    {
        $encoder = $this->getConfig()->video->encode->path->ffmpeg;

        $command = 
            "{$encoder} -v 2 -y -i {$this->getInputFile()} ".
            "{$this->getEncoderSettings()} {$this->getOutputFile()}.mov ".
            "&>{$this->getMonitorFile()}";
             
        return $command;
    }
    
    public function setEncoderSettings($string)
    {
        $this->_encoder_settings = $string;
    }
    
    public function getEncoderSettings()
    {
        return $this->_encoder_settings;
    }
    
    public function setFile($filename)
    {
        $this->setInputFile($filename);
        $this->setOutputFile($filename);
        $this->setMonitorFile($filename);
    }
    
    public function setInputFile($filename)
    {
        $path = $this->getConfig()->video->encode->path->input;
        $file = "{$path}/{$filename}";
        
        if(!file_exists($file)) {
            throw new Exception("JW_Video_Encode_Abstract::setInputFile(): {$file} does not exist.");
        }
        
        if(!is_readable($file)) {
            throw new Exception("JW_Video_Encode_Abstract::setInputFile(): {$file} is not readable.");
        }
        
        $this->_input_file = $file;
    }
    
    public function getInputFile()
    {
        if(null === $this->_input_file) {
            throw new Exception("JW_Video_Encode_Abstract::getInputFile(): Input file is not set.");
        }
        return $this->_input_file;
    }
    
    public function setOutputFile($filename)
    {
        $path = $this->getConfig()->video->encode->path->output;
        
        if(!file_exists($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} does not exist.");
        }
        
        if(!is_readable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} is not readable.");
        }
        
        if(!is_writeable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} is not writeable.");
        }
        
        $this->_output_file = "{$path}/{$filename}";
    }
    
    public function getOutputFile()
    {
        if(null === $this->_output_file) {
            throw new Exception("JW_Video_Encode_Abstract::getOutputFile(): Output file is not set.");
        }
        return $this->_output_file;
    }
    
    public function setMonitorFile($filename)
    {
        $path = $this->getConfig()->video->encode->path->monitor;
        
        if(!file_exists($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setMonitorFile(): {$path} does not exist.");
        }
        
        if(!is_readable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setMonitorFile(): {$path} is not readable.");
        }
        
        if(!is_writeable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setMonitorFile(): {$path} is not writeable.");
        }
        
        $this->_monitor_file = "{$path}/{$filename}.".time();
    }
    
    public function getMonitorFile()
    {
        if(null === $this->_monitor_file) {
            throw new Exception("JW_Video_Encode_Abstract::getMonitorFile(): Monitor file is not set.");
        }
        return $this->_monitor_file;
    }
    
    public function setConfig($config)
    {
        if(!is_a($config, 'Zend_Config')) {
            throw new Exception('JW_Video_Encode_Abstract::__construct(): $config must be an instance of Zend_Config.');
        }
        
        $this->_config = $config;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }

}