<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require_once(getenv("PROJECT_ROOT") . 'vendor/autoload.php');

// Create a simple "default" Doctrine ORM configuration for Attributes
$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/Entity'],
    isDevMode: true,
);

// configuring the database connection
$connection = DriverManager::getConnection([
    'driver' 	=> 'pdo_pgsql',
	'user' 		=> getenv('POSTGRES_USER'),
	'password' 	=> getenv('POSTGRES_PASSWORD'),
	'host' 		=> getenv('POSTGRES_DB'),
	'port' 		=> getenv('POSTGRES_PORT'),
	'dbname' 	=> getenv('POSTGRES_DB')
], $config);

// obtaining the entity manager
$entityManager = new EntityManager($connection, $config);