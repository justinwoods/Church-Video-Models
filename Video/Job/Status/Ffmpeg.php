<?php

class JW_Video_Job_Status_Ffmpeg
{

    const STATUS_STARTING = 'STARTING';
    const STATUS_ENCODING = 'ENCODING';
    const STATUS_FINISHED = 'FINISHED';
    const STATUS_ERROR    = 'ERROR';

    private $_filename		= null;
    private $_tail		= null;
    private $_current_frame	= null;
    private $_start_timestamp	= null;
    private $_total_frames	= null;

    private $_fields = array(
        'job_name'		=> 'getJobName',
        'status'		=> 'getStatus',
        'current_frame'		=> 'getFrame',
        'frames_per_second'	=> 'getFps',
        'start_timestamp'	=> 'getStartTimestamp',
        'time_elapsed' 		=> 'getTimeElapsed',
        'time_remaining' 	=> 'getTimeRemaining',
        'percent_complete'	=> 'getPercentComplete'
    );
    
    public function __construct($filename = null)
    {
        if(null === $filename) {
            return;
        }

        $this->setFilename($filename);
    }
    
    public function __get($name)
    {
        if(!in_array($name, array_keys($this->_fields))) {
            throw new Exception("JW_Video_Job_Status_Ffmpeg: {$name} is not a valid field.");
        }
        return $this->{$this->_fields[$name]}();
    }
    
    public function getAll()
    {
        foreach($this->_fields as $name=>$method) {
            $data[$name] = $this->{$name};
        }
        return $data;
    }
    
    public function setFilename($filename)
    {
        if(!file_exists($filename)) {
            throw new Exception("JW_Video_Job_Status_Ffmpeg: File {$filename} does not exist.");
        }
        
        if(!is_readable($filename)) {
            throw new Exception("JW_Video_Job_Status_Ffmpeg: File {$filename} is not readable.");
        }
        
        $this->_filename = $filename;
        $this->_current_frame = null;
        $this->_start_timestamp = null;
    }
    
    public function getFilename()
    {
        return $this->_filename;
    }
    
    public function getJobName()
    {
        return basename($this->getFilename());
    }

    public function getStatus()
    {
        $lines = $this->tail();
        
        foreach($lines as $num=>$line) {
            $pattern = '/^.*frame=\h*([0-9]+)/';
            $result = preg_match($pattern, $line, $matches);

            if(is_numeric($matches[1])) {
                $this->_current_frame = trim($matches[1]);
                
                if($num == 0) {
                    return self::STATUS_ENCODING;
                }
                
                if($this->_current_frame < ($this->getFrameCount() - 10)) {
                    return self::STATUS_ERROR;
                }
                
                return self::STATUS_FINISHED;
            }
        }
        return self::STATUS_STARTING;
    }

    public function getTotalFrames()
    {
        return $this->getFrameCount();
    }
    
    public function getRemainingFrames()
    {
        return ($this->getTotalFrames() - $this->getFrame());
    }
    
    public function getTimeRemaining()
    {
        if(0 == $this->getRemainingFrames()) {
            return 0;
        }

        if(0 == $this->getFps()) {
            return 0;
        }

        $remaining = ($this->getRemainingFrames() / $this->getFps());
        return floor($remaining);
    }
    
    public function getPercentComplete()
    {
        if(self::STATUS_FINISHED === $this->getStatus()) {
            return 100;
        }
        
        $percent = ($this->getFrame() / $this->getTotalFrames()) * 100;
        return round($percent, 2);
    }

    public function getFrame()
    {
        if(null === $this->_current_frame) {
            $this->getStatus();
        }
        return $this->_current_frame;
    }
    
    public function getStartTimestamp()
    {
        if(null === $this->_start_timestamp) {
            $file = $this->getFilename();
            $number = trim(substr($file, strrpos($file, '.') + 1));

            if(!is_numeric($number)) {
                throw new Exception("JW_Video_Job_Status_Ffmpeg: File {$filename} must have a UNIX timestamp as the file extension.");
            }
            if(1267900000 > $number) {
                throw new Exception("JW_Video_Job_Status_Ffmpeg: File {$filename} must have a UNIX timestamp as the file extension.");
            }
            $this->_start_timestamp = $number;
        }
        return $this->_start_timestamp;
    }
    
    public function getTimeElapsed()
    {
        return (filemtime($this->getFilename()) - $this->getStartTimestamp());
    }
    
    public function getFps()
    {
        return floor($this->getFrame() / $this->getTimeElapsed());
    }
    
    private function getLastLine()
    {
        $tail = $this->tail();
        return $tail[0];
    }
    
    private function tail()
    {
        if(null === $this->_tail) {
            $fp = fopen($this->getFilename(), 'r');
            fseek($fp, -2000, SEEK_END);
            $data = trim(fread($fp, 2000));
            $this->_tail = array_reverse(explode("\n",$data));
        }
        return $this->_tail;
    }

    public function getInputMediaFile() 
    {
        $pattern = '/^Input.*from\h\'(.*)\':/';
        $matches = $this->_getDataByRegex($pattern);
        return $matches[1];
    }

    public function getOutputMediaFile() 
    {
        $pattern = '/^Output.*from\h\'(.*)\':/';
        $matches = $this->_getDataByRegex($pattern);
        return $matches[1];
    }
    
    public function getDuration()
    {
        $pattern = '/^\h*Duration:\h*([0-9:.]*)/';
        $matches = $this->_getDataByRegex($pattern);
        list($hours, $minutes, $seconds) = explode(':', $matches[1]);
        $duration = ($hours * 3600) + ($minutes * 60) + $seconds;
        return $duration;
    }
    
    public function getFrameRate()
    {
        $pattern = '/^\h*Stream #.*Video:.*,\h([0-9.]*)\htbr/';
        $matches = $this->_getDataByRegex($pattern);
        return $matches[1];
    }
    
    public function getFrameCount()
    {
        return round(($this->getDuration() * $this->getFrameRate()));
    }
    
    private function _getDataByRegex($pattern)
    {
        $i = 0;
        $fp = fopen($this->getFilename(), 'r');
        while(!feof($fp)) {
            $line = fgets($fp);
            if(preg_match($pattern, $line, $matches)) {
                return $matches;
            }
            if($i++ == 50) {
                break;
            }
            
        }
        return null;
    }

}