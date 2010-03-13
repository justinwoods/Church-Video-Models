<?php

class JW_Video_Encode_Web extends JW_Video_Encode_Abstract
{

    protected $_settings = 
        '-vcodec libx264 -vpre hq -vpre ipod320';
    
    protected $_width		= 320;
    protected $_height		= 240;
    protected $_video_bitrate	= 300;
    protected $_audio_bitrate	= 64;

    protected $_file_extension	= 'mp4';

    public function getCommand()
    {
        return $this->_getCommand();
    }

}