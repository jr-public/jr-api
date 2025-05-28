<?php
namespace App\Bootstrap;

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Bootstrap\DoctrineBootstrap;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class DIContainerBootstrap {
    public static function create(): \Psr\Container\ContainerInterface {
        $builder = new ContainerBuilder();
		// $builder->useAutowiring(true);
        $builder->addDefinitions([
            EntityManagerInterface::class => \DI\factory(function () {
                return DoctrineBootstrap::create(); // returns EntityManager
            }),
        ]);
        $builder->addDefinitions([
            ValidatorInterface::class => \DI\factory(function () {
                return Validation::createValidatorBuilder()
                    ->enableAttributeMapping()
                    ->getValidator();
            }),
        ]);
        return $builder->build();
    }
}
