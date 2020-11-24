<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Factory;


use Symfony\Component\HttpFoundation\Session\Session;
use App\Domain\Facebook\FacebookMetricsClient;
use App\Factory\MetricsClientFactoyBase;
use App\Factory\IMetricsClient;
/**
 * Description of FacebookMetricsClientFactory
 *
 * @author Slavko
 */
class FacebookMetricsClientFactory extends MetricsClientFactoyBase {

    /**
     *
     * @var IMetricsClient 
     */
    static $instace;
    
    public function createInstace(Session $session, array $settings): IMetricsClient {
        if(null == self::$instace){
            self::$instace =    new FacebookMetricsClient($session, $settings);
        }
        self::$instace->SetSession($session); 
        return self::$instace;
    }
}
