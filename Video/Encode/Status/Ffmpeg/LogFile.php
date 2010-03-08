<?php

class JW_Video_Encode_Status_Ffmpeg_LogFile
{

    const DELIMITER 	= 'frame=';
    const TRIES		= 10;
    
    const STATUS_ENCODING = 'ENCODING';
    const STATUS_FINISHED = 'FINISHED';

    private $_filename		= null;
    private $_current_frame	= null;
    private $_start_timestamp	= null;
    private $_total_frames	= null;
    
    public function __construct($filename)
    {
        $this->setFilename($filename);
    }
    
    public function setFilename($filename)
    {
        if(!file_exists($filename)) {
            throw new Exception("JW_Video_Metadata_Ffmpeg_LogFile: File {$filename} does not exist.");
        }
        
        if(!is_readable($filename)) {
            throw new Exception("JW_Video_Metadata_Ffmpeg_LogFile: File {$filename} is not readable.");
        }
        
        $this->_filename = $filename;
        $this->_current_frame = null;
        $this->_start_timestamp = null;
    }
    
    public function getFilename()
    {
        return $this->_filename;
    }

    public function getStatus()
    {
        return (is_numeric($this->getFrame())) ? self::STATUS_ENCODING : self::STATUS_FINISHED;
    }

    public function getFrame()
    {
        if(null === $this->_current_frame) {
            $this->_current_frame = $this->_getFrame();
        }
        return $this->_current_frame;
    }
    
    public function getStartTimestamp()
    {
        if(null === $this->_start_timestamp) {
            $file = $this->getFilename();
            $number = trim(substr($file, strrpos($file, '.') + 1));

            if(!is_numeric($number)) {
                throw new Exception("JW_Video_Encode_Status_Ffmpeg_LogFile: File {$filename} must have a UNIX timestamp as the file extension.");
            }
            if(1267900000 > $number) {
                throw new Exception("JW_Video_Encode_Status_Ffmpeg_LogFile: File {$filename} must have a UNIX timestamp as the file extension.");
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
    
    public function update()
    {
        $this->_current_frame = null;
        $this->getFrame();
    }
    
    private function _getFrame()
    {
        $i = 0;
        while($i < self::TRIES) {
            while(null == ($status = $this->getLastLine())) {}
            
            $pattern = '/'.self::DELIMITER.'\h?([0-9]+)/';
            $result = preg_match($pattern, $status, $matches);
            
            if(is_numeric($matches[1])) {
                return trim($matches[1]);
            }
            
            $i++;
        }
    }
    
    private function getLastLine()
    {
    	$fp = fopen($this->getFilename(), 'r');
	$begining = fseek($fp, 0);      
	$pos = -1;
	$t = " ";
	
	while ($t != "\r") {
	    fseek($fp, $pos, SEEK_END);
	    if(ftell($fp) == $begining){
                break;
            }
            $t = fgetc($fp);
            $pos = $pos - 1;
        }
        $t = fgets($fp);
        fclose($fp);
        return $t;
    }

    private function getFirstLine()
    {
    	$fp = fopen($this->getFilename(), 'r');
    	$line = fgets($fp);
    	fclose($fp);
    	return trim($line);
    }
    
    public function getInputMediaFile() 
    {
        $i = 0;
        $pattern = '/^Input.*from\h\'(.*)\':/';
        $fp = fopen($this->getFilename(), 'r');
        while(!feof($fp)) {
            $line = fgets($fp);
            if(preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
            if($i++ == 50) {
                break;
            }
            
        }
        return null;
    }

}