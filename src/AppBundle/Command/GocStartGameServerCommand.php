<?php

namespace AppBundle\Command;

use AppBundle\Server\GameServer;
use Ratchet\Server\IoServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GocStartGameServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('goc:start-game-server')
            ->setDescription('Start Game of Code 2019 game server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new GameServer(),
            8080,
            '192.168.195.227');
        $server->run();
    }
}
