<?php
namespace App\Bootstrap;

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Bootstrap\DoctrineBootstrap;

class DIContainerBootstrap {
    public static function create(): \Psr\Container\ContainerInterface {
        $builder = new ContainerBuilder();
		// $builder->useAutowiring(true);
        $builder->addDefinitions([
            EntityManagerInterface::class => \DI\factory(function () {
                return DoctrineBootstrap::create(); // returns EntityManager
            }),
        ]);
        return $builder->build();
    }
}
