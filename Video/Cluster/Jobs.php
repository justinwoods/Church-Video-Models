<?php

class JW_Video_Cluster_Jobs
{

    private $_ec2	= null;
    private $_options	= null;
    
    public function __construct($options)
    {
        $this->_options = $options;
        $this->getAmazonEc2();
    }
    
    public function getServers()
    {
        $instances = $this->getAmazonEc2()->getGroupInstances('encode');
        return $instances;
    }
    
    public function getJobs()
    {
        $instances = $this->getServers();
        
        if(!is_array($instances)) {
            return array();
        }
        
        foreach($instances as $num=>$instance) {
            $instances[$num]['jobs'] = $this->getServerJobs($instance['dnsName']);
        }
        return $instances;
    }
    
    public function getServerJobs($hostname)
    {
        $url = "http://{$hostname}/api/";
        $jobs = json_decode(file_get_contents($url));
        
        if(!is_array($jobs)) {
            return array();
        }
        
        foreach($jobs as $job) {
            $list[] = $this->getJob($hostname, $job);
        }
        return $list;
    }
    
    public function getJob($hostname, $job)
    {
        $url = "http://{$hostname}/api/{$job}";
        $data = json_decode(file_get_contents($url));
        return (array) $data;
    }
    
    public function getAmazonEc2()
    {
        if(null === $this->_ec2) {
            $this->_ec2 = new JW_Service_Amazon_Ec2_Instances($this->_options);
        }
        return $this->_ec2;
    }

}