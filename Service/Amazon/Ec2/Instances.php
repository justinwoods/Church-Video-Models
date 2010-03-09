<?php

class JW_Service_Amazon_Ec2_Instances
{

    private $_ec2	= null;
    private $_config	= null;
    private $_instances = null;
    
    public function __construct($config)
    {
        $this->setConfig($config);
        $this->getAmazonEc2();
    }
    
    public function getGroupInstances($group)
    {
        $instances = $this->getRunningInstances();
        $list = array();

        foreach($instances['instances'] as $instance) {
            if(in_array($group, $instance['groupSet'])) {
                $list[] = $instance;
            }
        }
        return $list;
    }
    
    public function getRunningInstances()
    {
        if(null === $this->_instances) {
            $this->_instances = $this->_ec2->describe(null, true);
        }
        return $this->_instances;
    }
    
    public function getAmazonEc2()
    {
        if(null === $this->_ec2) {
            $this->_ec2 = new Zend_Service_Amazon_Ec2_Instance(
                $this->_config['amazon']['accessKeyId'],
                $this->_config['amazon']['secretAccessKey']);
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

}