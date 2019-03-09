<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 09/03/19
 * Time: 00:23
 */

namespace AppBundle\Model;


class SocketErrorEvent extends SocketEvent
{
    public function __construct(string $error)
    {
        parent::__construct('error', ['detail' => $error]);
    }
}
