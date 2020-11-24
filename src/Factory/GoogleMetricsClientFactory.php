<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Factory;

use Symfony\Component\HttpFoundation\Session\Session;
use App\Domain\Google\GoogleMetricsClient;
use App\Factory\MetricsClientFactoyBase;
/**

/**
 * Description of GoogleMetricsClientFactory
 *
 * @author Slavko
 */
class GoogleMetricsClientFactory extends MetricsClientFactoyBase {
    /**
     *
     * @var IMetricsClient 
     */
    static $instace;
    
    public function createInstace(Session $session, array $settings): IMetricsClient {
        if(null == self::$instace){
            self::$instace =  new GoogleMetricsClient($session, $settings);
        }
        self::$instace->SetSession($session); 
        return self::$instace;
    }

}