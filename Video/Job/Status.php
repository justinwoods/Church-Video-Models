<?php

class JW_Video_Job_Status
{

    public static function factory($type = null)
    {
        if(null === $type) {
            $type = 'Ffmpeg';
        }
        $class = 'JW_Video_Job_Status_'.ucfirst(strtolower($type));
        return new $class;
    }
    
    public function getJobList($monitor_path)
    {
        if(!file_exists($monitor_path)) {
            throw new Exception("JW_Video_Job_Status::getJobList(): {$path} does not exists.");
        }
        if(!is_readable($monitor_path)) {
            throw new Exception("JW_Video_Job_Status::getJobList(): {$path} is not readable.");
        }
        if(!is_dir($monitor_path)) {
            throw new Exception("JW_Video_Job_Status::getJobList(): {$path} is not a directory.");
        }
        
        $files = glob("{$monitor_path}/*");

        foreach($files as $file) {
            $jobs[] = basename($file);
        }

        return $jobs;
    }

}