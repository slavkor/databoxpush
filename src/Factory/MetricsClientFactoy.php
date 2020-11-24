<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Domain\Factory;

use Symfony\Component\HttpFoundation\Session\Session;

use App\Domain\Model\DataboxPushData;
use App\Domain\Model\WatchedObject;
use App\Domain\Factory\IMetricsClient;

/**
 * Description of MetricsClinet
 *
 * @author Slavko
 */
abstract class MetricsClientFactoy {
    abstract function createInstace(Session $session, array $settings) : IMetricsClient;
    /**
     *
     * @var IMetricsClient 
     */
    private $metricsclient;
    
    /**
     *
     * @var array
     */
    private $settings;
    
    /**
     * @var Session
     */
    private $session;
    
    
    public function __construct(Session $session, array $settings = []) {
        $this->session = $session;
        $this->settings = $settings;
    }
    
    public function GetWachedObject() : WatchedObject {
        if(null == $this->metricsclient){
            $this->metricsclient = $this->createInstace($this->session, $this->settings);
        }
        return $this->metricsclient->GetWathedObject();
    }
    
    public function GetMetricsFroWatchedObject(WatchedObject $object) :  DataboxPushData{
        if(null == $this->metricsclient){
            $this->metricsclient = $this->createInstace($this->session, $this->settings);
        }
        return $this->metricsclient->GetMetricsFroWatchedObject($object);
        
    }
}
