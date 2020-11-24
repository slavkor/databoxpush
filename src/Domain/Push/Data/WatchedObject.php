<?php



namespace App\Domain\Push\Data;

/**
 * WachedObject is the objectt to be wathed and it's metrics/properties to be pushed to databox service
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
