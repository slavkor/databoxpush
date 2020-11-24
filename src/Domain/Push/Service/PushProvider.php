<?php

namespace App\Domain\Push\Service;

use App\Domain\Push\Repository\PushProviderRepository;
use UnexpectedValueException;
use App\Domain\User\Data\UserAuthData;
use App\Domain\Push\Data\WatchedObject;
use App\Domain\Push\Data\DataboxPushData;

class PushProvider {
    /**
     *
     * @var PushProviderRepository 
     */
    private $repository;
    
    public function __construct(PushProviderRepository $repository) {
        $this->repository = $repository;
    }
    
    
    public function GetObject(UserAuthData $user): WatchedObject{
        return $this->repository->GetObject($user);
    }
    
    public function GetObjectMetrics(UserAuthData $user, WatchedObject $object) : DataboxPushData{
        return $this->repository->GetObjectMetrics($user, $object);
    }
    
    public function ExecutePush(UserAuthData $user, DataboxPushData $object) {
        return $this->repository->ExecutePush($user, $object);
    }

    /**
     * List all users.
     *
     * @param array $params The parameters
     *
     * @return array The result
     */
    public function listAllPushes(array $params): array
    {
        return $this->repository->getTableData($params);
    }
    
}
