<?php


namespace App\Domain\Push\Data;

/**
 * Metric represents a metric for an object
 *
 * @author Slavko
 */
final class Metric implements \JsonSerializable{
    /**
     * var string
     */
    private $key;
    
    /**
     * var string
     */
    private $value;
    
    public function __construct(string $key, string $value) {
        $this->key = $key;
        $this->value = $value;
    }
    
    public function getKey() {
        return $this->key;
    }

    public function getValue() {
        return $this->value;
    }

    public function setKey($key): void {
        $this->key = $key;
    }

    public function setValue($value): void {
        $this->value = $value;
    }

        
    public function jsonSerialize() {
        return [
            "key" => $this->key,
            "value" => $this->value
        ];
    }
}
