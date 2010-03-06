<?php

class JW_Video_Encode_Status
{

    public static function factory($type = null)
    {
        if(null === $type) {
            $type = 'Ffmpeg';
        }
        $class = 'JW_Video_Encode_Status_'.ucfirst(strtolower($type));
        return new $class;
    }

}