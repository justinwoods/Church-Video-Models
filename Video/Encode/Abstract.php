<?php

abstract class JW_Video_Encode_Abstract
{

    const TARGET_ASPECT_43	= '4:3';
    const TARGET_ASPECT_169	= '16:9';

    private $_config		= null;
    private $_input_file	= null;
    private $_output_file	= null;
    private $_monitor_file	= null;
    private $_monitor_metadata	= null;
    private $_padding		= null;
    
    private $_metadata		= null;
    
    protected $_settings	= null;
    protected $_width		= null;
    protected $_height		= null;
    protected $_video_bitrate	= null;
    protected $_audio_bitrate	= null;
    protected $_audio_channels	= 1;
        
    public function __construct($config)
    {
        $this->setConfig($config);
    }
    
    protected function _getCommand()
    {
        $encoder = $this->_config['video']['encode']['path']['ffmpeg'];

        $command = 
            "echo '{$this->getMonitorMetadata()}".
            JW_Video_Job_Status_Ffmpeg::METADATA_DELIMITER.
            "' > {$this->getMonitorFile()};".
            "{$encoder} -v 2 -y -i {$this->getInputFile()} -async 10000 ".
            "{$this->_getEncoderSettings()} {$this->getOutputDimensions()} ".
            "{$this->_getBitrateSettings()} {$this->_getAudioSettings()} {$this->getOutputFile()} ".
            "2>> {$this->getMonitorFile()};";
        
        return $command;
    }
    
    private function _getBitrateSettings()
    {
        return "-b {$this->getVideoBitrate()}k -ab {$this->getAudioBitrate()}k -ar 48000";
    }
    
    private function _getAudioSettings()
    {
        return "-ac {$this->getAudioChannels()}";
    }
    
    private function _getSizeSettings()
    {
        return "-s {$this->getWidth()}x{$height}";
    }
    
    private function _getPaddingSettings()
    {
        if(0 >= $this->getPadding()) {
            return '';
        }
        
        return "-padtop {$this->getPaddingTop()} -padbottom {$this->getPaddingBottom()} -padcolor 000000";
    }
    
    public function getOutputDimensions()
    {
	$target_width  = $this->getWidth();
	$target_height = $this->getHeight();

	$original_width  = $this->getMetadata()->width;
	$original_height = $this->getMetadata()->height;

        $padding = array();
        $aspect = $target_width / $target_height;
        
        if($original_width/$original_height !== $aspect)
        {
            // Aspect ratio is different
            if($original_width/$original_height > $aspect)
            {
                // Width is the greater of the two dimensions relative to the target dimensions
                // Calculate height from width
                $original_height = round($original_height / $original_width * $target_width);
                $original_width = $target_width;
                
                $dif = round(($target_height - $original_height) / 2);
                $padding['padtop'] = $dif;
                $padding['padbottom'] = $dif;
                
                $target_height = $target_height - 2*$dif;
            }
            else
            {
                // Height is the greater of the two dimensions relative to the target dimensions
                //Calculate width from height
                $original_width = round($original_width / $original_height * $target_height);
                $original_height = $target_height;
                
                $dif = round(($target_width - $original_width) / 2);
                $padding['padleft'] = $dif;
                $padding['padright'] = $dif;
                
                $target_width = $target_width - 2*$dif;
            }
        }
        
        foreach($padding as $k=>$v) {
            $target_padding .= " -{$k} {$v}";
        }

        return "-s {$target_width}x{$target_height}{$target_padding}";
    }
    
    public function getMetadata()
    {
        if(null === $this->_metadata) {
            $this->_metadata = JW_Video_Metadata::factory();
            $this->_metadata->setFilename($this->getInputFile());
        }
        return $this->_metadata;
    }
    
    public function setWidth($width)
    {
        if(!is_numeric($width)) {
            throw new Exception('JW_Video_Encode_Abstract::setWidth(): $width must be a number.');
        }
        if($width <= 0) {
            throw new Exception('JW_Video_Encode_Abstract::setWidth(): $width must be positive.');
        }
        $this->_width = $width;
    }
    
    public function getWidth()
    {
        return $this->_width;
    }

    public function setHeight($height)
    {
        if(!is_numeric($height)) {
            throw new Exception('JW_Video_Encode_Abstract::setHeight(): $height must be a number.');
        }
        if($height <= 0) {
            throw new Exception('JW_Video_Encode_Abstract::setHeight(): $height must be positive.');
        }
        $this->_height = $height;
    }
    
    public function getHeight()
    {
        return $this->_height;
    }
    
    public function setAudioBitrate($bitrate)
    {
        if(!is_numeric($bitrate)) {
            throw new Exception('JW_Video_Encode_Abstract::setAudioBitrate(): $bitrate must be a number.');
        }
        if($bitrate <= 0) {
            throw new Exception('JW_Video_Encode_Abstract::setAudioBitrate(): $bitrate must be positive.');
        }
        $this->_audio_bitrate = $bitrate;
    }
    
    public function getAudioBitrate()
    {
        return $this->_audio_bitrate;
    }
    
    public function setVideoBitrate($bitrate)
    {
        if(!is_numeric($bitrate)) {
            throw new Exception('JW_Video_Encode_Abstract::setVideoBitrate(): $bitrate must be a number.');
        }
        if($bitrate <= 0) {
            throw new Exception('JW_Video_Encode_Abstract::setVideoBitrate(): $bitrate must be positive.');
        }
        $this->_video_bitrate = $bitrate;
    }
    
    public function getVideoBitrate()
    {
        return $this->_video_bitrate;
    }
    
    public function setAudioChannels($channels)
    {
        if(!is_numeric($channels)) {
            throw new Exception('JW_Video_Encode_Abstract::setAudioChannels(): $channels must be an integer.');
        }
        
        $this->_audio_channels = (int) $channels;
    }
    
    public function getAudioChannels()
    {
        return $this->_audio_channels;
    }
    
    public function setEncoderSettings($string)
    {
        $this->_settings = $string;
    }
    
    private function _getEncoderSettings()
    {
        return $this->_settings;
    }
    
    public function setFile($filename)
    {
        $this->setInputFile($filename);
        $this->setOutputFile($filename);
        $this->setMonitorFile($filename);
    }
    
    public function setInputFile($filename)
    {
        $path = $this->_config['video']['encode']['path']['input'];
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
        $path = $this->_config['video']['encode']['path']['output'];
        
        if(!file_exists($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} does not exist.");
        }
        
        if(!is_readable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} is not readable.");
        }
        
        if(!is_writeable($path)) {
            throw new Exception("JW_Video_Encode_Abstract::setOutputFile(): {$path} is not writeable.");
        }
        
        $filename = substr($filename, 0, strpos($filename, '.'));
        
        $this->_output_file = "{$path}/{$filename}.{$this->_file_extension}";
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
        $path = $this->_config['video']['encode']['path']['monitor'];
        
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
    
    public function setMonitorMetadata($data)
    {
        $this->_monitor_metadata = $data;
    }
    
    public function getMonitorMetadata()
    {
        $formatter = new JW_Model_Rfc822;
        $formatter->addData($this->_monitor_metadata);
        return (string) $formatter;
    }
    
    public function setConfig($config)
    {
        $this->_config = $config;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }

}