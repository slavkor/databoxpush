<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Factory;

use App\Domain\Model\DataboxPushData;
use App\Domain\Model\WatchedObject;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Description of IMetricsClient
 *
 * @author Slavko
 */
interface IMetricsClient {
    public function GetWathedObject(): WatchedObject ;
    public function GetMetricsFroWatchedObject(WatchedObject $object): DataboxPushData;
    public function SetSession(Session $session);
}
