<?php

class JW_Video_Encode_Archive extends JW_Video_Encode_Abstract
{

    protected $_settings = 
        '-vcodec libx264 -vpre hq';
    
    protected $_width		= 640;
    protected $_height		= 480;
    protected $_video_bitrate	= 2048;
    protected $_audio_bitrate	= 128;

    public function getCommand()
    {
        return $this->_getCommand();
    }

}