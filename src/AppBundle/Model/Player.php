<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 22:15
 */

namespace AppBundle\Model;


use Ratchet\ConnectionInterface;

class Player extends AClient
{
    /**
     * @var string
     */
    private $pseudo;

    public function __construct(ConnectionInterface $connection, string $pseudo)
    {
        parent::__construct($connection);
        $this->pseudo = $pseudo;
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }
}
