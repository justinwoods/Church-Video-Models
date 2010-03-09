<?php

class JW_Video_Encode_Status_Ffmpeg extends JW_Video_Encode_Status_Abstract
{

    private $_logfile_object = null;
    
    private $_logfile_fields	= array(
        'status'		=> 'getStatus',
        'current_frame'		=> 'getFrame',
        'frames_per_second'	=> 'getFps',
        'start_timestamp'	=> 'getStartTimestamp',
        'time_elapsed' 		=> 'getTimeElapsed',
    );

    private $_fields		= array(
        'time_remaining' 	=> 'getTimeRemaining',
        'percent_complete'	=> 'getPercentComplete'
    );
    
    public function __get($name)
    {
        parent::__get($name);
        
        switch(true) {

            case (in_array($name, array_keys($this->_logfile_fields))):
                $method = $this->_logfile_fields[$name];
                return $this->getLogFileObject()->{$method}();

            case (in_array($name, array_keys($this->_fields))):
                $method = $this->_fields[$name];
                return $this->{$method}();
        }
        
    }
    
    public function getAll()
    {
        $fields = array_merge($this->_logfile_fields, $this->_fields);
        foreach($fields as $name=>$method) {
            $data[$name] = $this->{$name};
        }
        return $data;
    }

    public function getLogFileObject($filename = null)
    {
        if(null != $filename) {
            $this->setFilename($filename);
            return $this->getLogFileObject();
        }

        if(null === $this->_logfile_object) {
            $this->_logfile_object = new JW_Video_Encode_Status_Ffmpeg_LogFile($this->getFilename());
        }
        return $this->_logfile_object;
    }
    
}