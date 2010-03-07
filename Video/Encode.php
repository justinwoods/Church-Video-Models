<?php

class JW_Video_Encode
{

    const ENCODE_TYPE_ARCHIVE	= 'ARCHIVE';
    const ENCODE_TYPE_AUDIO	= 'AUDIO';
    const ENCODE_TYPE_DVD	= 'DVD';
    const ENCODE_TYPE_WEB	= 'WEB';
    
    private static $_valid_types = array(
        ENCODE_TYPE_ARCHIVE, ENCODE_TYPE_AUDIO,
        ENCODE_TYPE_DVD, ENCODE_TYPE_WEB
    );

    public static function factory($type, $config)
    {
        $class = 'JW_Video_Encode_'.ucfirst(strtolower($type));
        return new $class($config);
    }

}