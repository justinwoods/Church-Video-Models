<?php

class JW_Model_Encode
{

    private $_sqs		= null;
    private $_config		= null;
    private $_job_message	= null;
    private $_cluster_jobs	= null;
    
    public function __construct($config, $sqs, $s3)
    {
        $this->setConfig($config);

        $this->setAmazonSqs($sqs);
        $this->getAmazonSqs()->setGetNumMessages(1);
        
        $this->setAmazonS3($s3);
    }
    
    public function doJob() 
    {
        if(null === ($job = $this->getJob())) {
            throw new Exception('JW_Model_Encode::doJob(): No job');
        }
    
        $e = JW_Video_Encode::factory($job->output_format, $this->getConfig());
        $e->setFile(basename($job->permanentFilename));
        $e->setMonitorMetadata($job->toArray());
        $command = $e->getCommand();

        $this->getAmazonSQS()->log(array('command'=>$command));
        
        popen($command, 'r');
    }
    
    public function putMedia()
    {
        $config = $this->getConfig();
        $job = $this->getJob();

        $response = $this->getAmazonS3()->putFileStream(
            $config['video']['encode']['path']['output'].'/'.basename($job->permanentFilename),
            'church-video-encoded/blah.mov'
        );

        if(false == $response) {
            throw new Exception("JW_Model_Encode::putMedia(): Error uploading {$job->permanentFilename}");
        }
    
    }
    
    public function getMedia()
    {
        if(null === ($job = $this->getJob())) {
            throw new Exception('JW_Model_Encode::getMedia(): No job');
        }
    
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
            list($job) = $this->getAmazonSqs()->encode();
            
            if(count($job) > 0) {
  
                # Check whether the job is already being processed elsewhere
                if(count($this->getClusterJobs()->getJobByMessageId($job->message_id)) > 0) {
                    continue;
                }
                
                # Check whether output file is already complete and in permanent storage
                if($this->getAmazonS3()->isObjectAvailable('')) {
                    continue;
                }

                $this->_job_message = $job;
                return $this->_job_message;
            }

            if($i++ == 10) {
                break;
            }
            sleep(1);
        }
        
    }
    
    public function deleteJob()
    {
        $this->getAmazonSqs()->deleteMessage(
            $this->getAmazonSqs()->getUrl('encode'),
            $this->getJob()->handle
        );
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
    
    public function getClusterJobs()
    {
        if(null === $this->_cluster_jobs) {
            $this->_cluster_jobs = new JW_Video_Cluster_Jobs($this->getConfig());
        }
        return $this->_cluster_jobs;
    }

}