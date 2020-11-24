<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Domain\Push\Data;

/**
 * Description of WachedObject
 *
 * @author Slavko
 */
final class WatchedObject implements \JsonSerializable{
    
    /**
     *
     * @var string
     */
    private $objectId;
    
    /**
     *
     * @var array
     */
    private $objectProperties;
    
    public function getObjectId(): ?string {
        return $this->objectId;
    }

    public function getObjectProperties(): ?array {
        return $this->objectProperties;
    }

    public function setObjectId(string $objectId): void {
        $this->objectId = $objectId;
    }

    public function setObjectProperties(array $objectProperties): void {
        $this->objectProperties = $objectProperties;
    }

    public function jsonSerialize() {
        return [
            "objectId" => $this->objectId,
            "metrics" => $this->objectProperties
        ];
    }
}
