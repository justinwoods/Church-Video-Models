<?php

class JW_Video_Metadata_Abstract
{
    
    protected $_filename = null;

    private $_valid_fields = array(
        'duration', 'frame_count', 'frame_rate', 'filename', 'comment',
        'title', 'author', 'copyright', 'frame_height', 'frame_width',
        'pixel_format', 'bitrate', 'video_bitrate', 'audio_bitate',
        'audio_sample_rate', 'video_codec', 'audio_codec', 'audio_channels',
        'has_audio', 'has_video'
    );
    
    public function __get($name)
    {
        if(!in_array($name, $this->_valid_fields)) {
            throw new Exception("JW_Video_Metadata_Abstract: {$name} is not a valid field.");
        }
    }
    
    
    public function setFilename($filename)
    {
        if(!file_exists($filename)) {
            throw new Exception("JW_Video_Metadata_Abstract: File {$filename} does not exist.");
        }
        if(!is_readable($filename)) {
            throw new Exception("JW_Video_Metadata_Abstract: File {$filename} is not readable.");
        }

        $this->_filename = $filename;
    }
    
    public function getFilename()
    {
        return $this->_filename;
    }
    
}
