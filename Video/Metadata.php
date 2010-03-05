<?php

class JW_Video_Metadata
{

    public static function factory($type = null)
    {
        if(null === $type) {
            $type = 'Ffmpeg';
        }
        $class = 'JW_Video_Metadata_'.ucfirst(strtolower($type));
        return new $class;
    }

}