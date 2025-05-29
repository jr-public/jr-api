<?php
namespace App\Bootstrap;

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use App\Bootstrap\DoctrineBootstrap;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;


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
        $builder->addDefinitions([
            Request::class => \DI\factory(function () {
                return Request::createFromGlobals();
            }),
        ]);
        return $builder->build();
    }
}
