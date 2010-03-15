<?php

class JW_Cluster_EncodeJobs
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
        
        $jobs = array();
        foreach($instances as $num=>$instance) {
            $server_jobs = $this->getServerJobs($instance['dnsName']);
            foreach($server_jobs as $job_num=>$job) {
                $server_jobs[$job_num]['server'] = $instance['dnsName'];
            }
            $jobs = array_merge($jobs, $server_jobs);
        }
        return $jobs;
    }
        
    public function getJobListByField($field)
    {
        $jobs = $this->getJobs();
        
        $list = array();
        foreach($jobs as $job) {
            $list[] = $job['upload_id'];
        }
        
        return $list;
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
    
    public function findJobs($key, $value)
    {
        $jobs = $this->getJobs();

        $matches = array();
        foreach($jobs as $job) {
            if($job[$key] == $value) {
                $matches[] = $job;
            }
        }
        return $matches;
    }
    
    public function getJobByMessageId($message_id)
    {
        return $this->findJobs('message_id', $message_id);
    }
    
    public function getAmazonEc2()
    {
        if(null === $this->_ec2) {
            $this->_ec2 = new JW_Service_Amazon_Ec2_Instances($this->_options);
        }
        return $this->_ec2;
    }

}