<?php

class JW_Model_Ingest
{

    private $_ec2	= null;
    private $_sqs	= null;
    private $_config	= null;
    
    public function __construct($config, $sqs)
    {
        $this->setConfig($config);
        $this->setAmazonSqs($sqs);
    }
    
    public function processQueue()
    {
        $messages = $this->getAmazonSqs()->ingest();
    
        foreach($messages as $message) {
            print_r($message);

            $body = json_decode(base64_decode($message['body']));
            
            print_r($body);
        
            $instance = $this->startInstance($body[0]->permanent);

            # Put this job on the encode queue
            $this->getAmazonSqs()->encode($body[0]->permanent);

        }
    }
    
    public function startInstance($userdata)
    {
        $ec2 = $this->getAmazonEc2();
        $config = $this->getConfig();
        
        $instance = $ec2->run(array(
            'imageId' 	=> $config['amazon']['ec2']['instance']['encode']['imageId'],
            'keyName' 	=> $config['amazon']['ec2']['instance']['encode']['keyName'],
            'securityGroup' => $config['amazon']['ec2']['instance']['encode']['securityGroup'],
            'userData'	=> $userdata
        ));

        if(false === $instance) {
            throw new Exception("Model_Ingest::startInstance(): Could not start instance.\nuserData: {$userdata}");
        }

        $log_message = "Started EC2 instance: {$instance['instances'][0]['instanceId']}";
        Zend_Registry::get('logger')->debug($log_message);
        
        return $instance;
    }
        
    public function getAmazonEc2()
    {
        if(null === $this->_ec2) {
            $config = $this->getConfig();

            $this->_ec2 = new Zend_Service_Amazon_Ec2_Instance(
                $config['amazon']['accessKeyId'],
                $config['amazon']['secretAccessKey']
            );
        }
        return $this->_ec2;
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

}