<?php

class JW_Video_Metadata_Ffmpeg extends JW_Video_Metadata_Abstract
{

    private $_ffmpeg_object = null;
    
    private $_field_map	= array(
        'duration'		=> 'getDuration',
        'frame_count'		=> 'getFrameCount',
        'frame_rate'		=> 'getFrameRate',
        'filename'		=> 'getFilename',
        'comment'		=> 'getComment',
        'title'			=> 'getTitle',
        'author' 		=> 'getAuthor',
        'copyright' 		=> 'getCopyright',
        'width'			=> 'getFrameWidth',
        'height' 		=> 'getFrameHeight',
        'pixel_format' 		=> 'getPixelFormat',
        'bitrate' 		=> 'getBitRate',
        'video_bitrate' 	=> 'getVideoBitRate',
        'audio_bitate'		=> 'getAudioBitRate',
        'audio_sample_rate' 	=> 'getAudioSampleRate',
        'video_codec' 		=> 'getVideoCodec',
        'audio_codec' 		=> 'getAudioCodec',
        'audio_channels'	=> 'getAudioChannels',
        'has_audio' 		=> 'hasAudio',
        'has_video' 		=> 'hasVideo'
    );

    public function __get($name)
    {
        parent::__get($name);
        
        $method = $this->_field_map[$name];
        return $this->getFfmpegObject()->{$method}();
    }
    
    public function getAll()
    {
        foreach($this->_field_map as $name=>$method) {
            $data[$name] = $this->getFfmpegObject()->{$method}();
        }
        return $data;
    }
    
    public function getFrame($number)
    {
        return $this->getFfmpegObject()->getFrame($number)->toGDImage();
    }
    
    public function getFfmpegObject($filename = null)
    {
        if(null != $filename) {
            $this->setFilename($filename);
            return $this->getFfmpegObject();
        }

        if(null === $this->_ffmpeg_object) {
            $this->_ffmpeg_object = new ffmpeg_movie($this->getFilename());
        }
        return $this->_ffmpeg_object;
    }
    
    public function setFilename($filename)
    {
        parent::setFilename($filename);
        $this->_ffmpeg_object = null;
        $this->getFfmpegObject();
    }

}