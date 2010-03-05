<?php

class JW_Model_Rfc822
{

    private $_data	= null;
    private $_num	= 0;
    private $_prefix	= "_Field";
    
    # Add data. $raw_data should be a RFC-822 formatted string
    public function addRawData($raw_data)
    {
        $this->_addRawData($raw_data);
    }
    
    protected function _addRawData($raw_data)
    {
        if(!is_string($raw_data)) {
            throw new Exception("JW_Model_Rfc822::addData(): Fieldname must be a string.");
        }
        
        $lines = explode("\n", trim($raw_data));
        foreach($lines as $line) {
            $colon_pos = strpos($line, ':');
            $name  = substr($line, 0, $colon_pos);
            $value = substr($line, $colon_pos + 1);
            $this->addData($value, $name);
        }
    }
    
    public function clear()
    {
        $this->_data = null;
    }
    
    public function addData($data, $fieldname = null)
    {
        if(is_array($data)) {
            foreach($data as $k=>$v) {
                $this->_data[$this->_clean($k)] = $this->_clean($v);
            }
        }
        elseif(is_scalar($data)) {
            if(!is_null($fieldname) && !is_string($fieldname)) {
                throw new Exception("JW_Model_Rfc822::addData(): Fieldname must be a string.");
            }
    
            if((null === $fieldname)) {
                $fieldname = $this->_prefix.$this->_num++;
            }
            $this->_data[$this->_clean($fieldname)] = $this->_clean($data);
        }
    }
    
    private function _clean($value)
    {
        if(!is_scalar($value)) {
            throw new Exception("JW_Model_Rfc822::_clean(): Value must be a scalar.");
        }
    
        return trim(str_replace("\n", ' ', $value));
    }
    
    protected function _formatOutput()
    {
        foreach($this->_data as $k=>$v) {
            $output .= "{$k}: {$v}\n";
        }
        
        return $output;
    }
    
    public function __toString()
    {
        return $this->_formatOutput();
    }
    
    public function __set($name, $value)
    {
        if(!is_scalar($value)) {
            throw new Exception("JW_Model_Rfc822::__set(): Value must be a scalar.");
        }
        
        $this->addData($value, $name);
    }
    
    public function __get($name)
    {
        return $this->_data[$name];
    }
    
    public function toString()
    {
        return $this->__toString();
    }
    
    public function toArray()
    {
        return $this->_data;
    }

}