<?php

class JW_Video_Encode_Status_Abstract
{
    
    protected $_filename = null;

    private $_valid_fields = array(
        'status', 'current_frame', 'frames_per_second', 'start_timestamp', 
        'time_elapsed', 'time_remaining', 'percent_complete'
    );
    
    public function __get($name)
    {
        if(!in_array($name, $this->_valid_fields)) {
            throw new Exception("JW_Video_Encode_Status_Abstract: {$name} is not a valid field.");
        }
    }
    
    public function getTotalFrames()
    {
        return $this->getMetadataObject()->frame_count;
    }
    
    public function getRemainingFrames()
    {
        return ($this->getTotalFrames() - $this->getLogFileObject()->getFrame());
    }
    
    public function getTimeRemaining()
    {
        $remaining = ($this->getRemainingFrames() / $this->getLogFileObject()->getFps());
        return floor($remaining);
    }
    
    public function getPercentComplete()
    {
        $percent = ($this->getLogFileObject()->getFrame() / $this->getTotalFrames()) * 100;
        return round($percent, 2);
    }
    
    public function setFilename($filename)
    {
        if(!file_exists($filename)) {
            throw new Exception("JW_Video_Encode_Status_Abstract: File {$filename} does not exist.");
        }
        if(!is_readable($filename)) {
            throw new Exception("JW_Video_Encode_Status_Abstract: File {$filename} is not readable.");
        }

        $this->_filename = $filename;
    }
    
    public function getFilename()
    {
        return $this->_filename;
    }
    
}
