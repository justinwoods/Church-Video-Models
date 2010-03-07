<?php

class JW_Video_Encode
{

    const ENCODE_TYPE_ARCHIVE	= 'ARCHIVE';
    const ENCODE_TYPE_AUDIO	= 'AUDIO';
    const ENCODE_TYPE_DVD	= 'DVD';
    const ENCODE_TYPE_WEB	= 'WEB';
    
    private $_valid_types = array(
        ENCODE_TYPE_ARCHIVE, ENCODE_TYPE_AUDIO,
        ENCODE_TYPE_DVD, ENCODE_TYPE_WEB
    );

    public static function factory($type = null)
    {
        if(!in_array($type, $this->_valid_types)) {
            throw new Exception("JW_Video_Encode::factory(): {$type} is not a valid type.");
        }
    
        $class = 'JW_Video_Encode_'.ucfirst(strtolower($type));
        return new $class;
    }

}