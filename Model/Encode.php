<?php

class JW_Model_Encode
{

    private $_sqs		= null;
    private $_config		= null;
    private $_job_message	= null;
    
    public function __construct($config, $sqs, $s3)
    {
        $this->setConfig($config);

        $this->setAmazonSqs($sqs);
        $this->getAmazonSqs()->setGetNumMessages(1);
        
        $this->setAmazonS3($s3);
    }
    
    public function doJob() 
    {
        $e = JW_Video_Encode::factory(JW_Video_Encode::ENCODE_TYPE_WEB, $this->getConfig());
        $e->setFile(basename($this->getJob()->permanentFilename));
        $e->setMonitorMetadata($this->getJob()->toArray());
        echo $e->getCommand();
    }
    
    public function getMedia()
    {
        $job = $this->getJob();
        $config = $this->getConfig();
        
        $response = $this->getAmazonS3()->getObjectStream(
            $job->permanentFilename,
            $config['video']['encode']['path']['input'].'/'.basename($job->permanentFilename)
        );
        
        if(false == $response) {
            throw new Exception("JW_Model_Encode::getMedia(): Error downloading {$job->permanentFilename}");
        }
    }
    
    public function getJob()
    {
        if(null !== $this->_job_message) {
            return $this->_job_message;
        }
    
        $i = 0;
        while(true) {
            $job = $this->getAmazonSqs()->encode();

            if(count($job) > 0) {
                $this->_job_message = $job[0];
                return $this->_job_message;
            }

            if($i++ == 10) {
                break;
            }
            sleep(1);
        }
        
    }
    
    public function setConfig($config)
    {
        $this->_config = $config;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function setAmazonSqs($sqs)
    {
        $this->_sqs = $sqs;
    }
    
    public function getAmazonSqs()
    {
        return $this->_sqs;
    }

    public function setAmazonS3($s3)
    {
        $this->_s3 = $s3;
    }
    
    public function getAmazonS3()
    {
        return $this->_s3;
    }

}