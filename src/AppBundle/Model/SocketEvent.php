<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 23:49
 */

namespace AppBundle\Model;

use InvalidArgumentException;

class SocketEvent
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $payload;

    public function __construct(string $raw = null, array $payload = null)
    {
        if (is_null($raw))
            return;
        if (is_null($payload))
            $this->setRawJson($raw);
        else {
            $this->setType($raw);
            $this->setPayload($payload);
        }
    }

    public function setRawJson(string $rawJson)
    {
        $data = json_decode($rawJson, true);
        if (!$data
            || !isset($data['type'])
            || !isset($data['payload'])
            || !is_string($data['type'])
            || !is_array($data['payload']))
            throw new InvalidArgumentException("Incorrect json");
        $this->type = $data['type'];
        $this->payload = $data['payload'];
    }

    public function getRawJson() : string
    {
        return json_encode([
            'type' => $this->type,
            'payload' => $this->payload,
        ]);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}
