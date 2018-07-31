<?php
namespace CRUDlexUserTestEnv;

use CRUDlex\EntityDefinitionFactory;
use CRUDlex\EntityDefinitionValidator;
use Doctrine\DBAL\Connection;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

use Eloquent\Phony\Phpunit\Phony;

use CRUDlex\MySQLDataFactory;
use CRUDlex\Service;
use CRUDlex\UserSetup;
use CRUDlexUserTestEnv\NullFileProcessor;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\Translator;

class TestDBSetup {

    private static $filesystemHandle;

    public static function getDBConfig()
    {
        return [
            'host'      => '127.0.0.1',
            'dbname'    => 'crudTest',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'driver' => 'pdo_mysql',
        ];
    }

    public static function createDB(Connection $db, $useManyToMany)
    {

        $db->executeUpdate('DROP TABLE IF EXISTS password_reset;');
        $db->executeUpdate('DROP TABLE IF EXISTS user_role;');
        $db->executeUpdate('DROP TABLE IF EXISTS role;');
        $db->executeUpdate('DROP TABLE IF EXISTS user;');

        $db->executeUpdate('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
        $db->executeUpdate('SET time_zone = "+00:00"');

        $sql = 'CREATE TABLE `role` ('.
            '  `id` int(11) NOT NULL AUTO_INCREMENT,'.
            '  `version` int(11) NOT NULL,'.
            '  `created_at` datetime NOT NULL,'.
            '  `updated_at` datetime NOT NULL,'.
            '  `deleted_at` datetime DEFAULT NULL,'.
            '  `role` varchar(255) NOT NULL,'.
            '  PRIMARY KEY (`id`)'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $db->executeUpdate($sql);

        $sql = 'CREATE TABLE `user` ('.
            '  `id` int(11) NOT NULL AUTO_INCREMENT,'.
            '  `created_at` datetime NOT NULL,'.
            '  `updated_at` datetime NOT NULL,'.
            '  `deleted_at` datetime DEFAULT NULL,'.
            '  `version` int(11) NOT NULL,'.
            '  `password` varchar(255) DEFAULT NULL,'.
            '  `salt` varchar(255) NOT NULL,'.
            '  `username` varchar(255) NOT NULL,'.
            '  `email` varchar(255) NOT NULL,'.
            '  PRIMARY KEY (`id`)'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $db->executeUpdate($sql);

        if ($useManyToMany) {
            $sql = 'CREATE TABLE `user_role` ('.
                '  `user` int(11) NOT NULL,'.
                '  `role` int(11) NOT NULL,'.
                '  KEY `user` (`user`),'.
                '  KEY `role` (`role`),'.
                '  CONSTRAINT `userrole_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`),'.
                '  CONSTRAINT `userrole_ibfk_2` FOREIGN KEY (`role`) REFERENCES `role` (`id`)'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $db->executeUpdate($sql);
        } else {
            $sql = 'CREATE TABLE `user_role` ('.
                '  `id` int(11) NOT NULL AUTO_INCREMENT,'.
                '  `version` int(11) NOT NULL,'.
                '  `created_at` datetime NOT NULL,'.
                '  `updated_at` datetime NOT NULL,'.
                '  `deleted_at` datetime DEFAULT NULL,'.
                '  `user` int(11) NOT NULL,'.
                '  `role` int(11) NOT NULL,'.
                '  PRIMARY KEY (`id`),'.
                '  KEY `user` (`user`),'.
                '  KEY `role` (`role`),'.
                '  CONSTRAINT `userrole_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`),'.
                '  CONSTRAINT `userrole_ibfk_2` FOREIGN KEY (`role`) REFERENCES `role` (`id`)'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $db->executeUpdate($sql);
        }

        $sql = 'CREATE TABLE `password_reset` ('.
            '  `id` int(11) NOT NULL AUTO_INCREMENT,'.
            '  `version` int(11) NOT NULL,'.
            '  `created_at` datetime NOT NULL,'.
            '  `updated_at` datetime NOT NULL,'.
            '  `deleted_at` datetime DEFAULT NULL,'.
            '  `user` int(11) NOT NULL,'.
            '  `token` varchar(255) NOT NULL,'.
            '  `reset` datetime DEFAULT NULL,'.
            '  PRIMARY KEY (`id`),'.
            '  KEY `user` (`user`),'.
            '  CONSTRAINT `passwordreset_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`)'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $db->executeUpdate($sql);
    }

    public static function createService($useManyToMany)
    {


        $config = new \Doctrine\DBAL\Configuration();
        $db = \Doctrine\DBAL\DriverManager::getConnection(TestDBSetup::getDBConfig(), $config);
        static::createDB($db, $useManyToMany);
        $crudFile =  __DIR__.'/../'.($useManyToMany ? 'crudManyToMany.yml' : 'crud.yml');
        $dataFactory = new MySQLDataFactory($db);
        $filesystem = new Filesystem(new NullAdapter());
        $validator = new EntityDefinitionValidator();
        $routes = new RouteCollection();
        $context = new RequestContext();
        $urlGenerator = new UrlGenerator($routes, $context);

        $translator = new Translator('en');
        $entityDefinitionFactory = new EntityDefinitionFactory();
        $service = new Service($crudFile, null, $urlGenerator, $translator, $dataFactory, $entityDefinitionFactory, $filesystem, $validator);

        static::$filesystemHandle = Phony::partialMock('\\League\\Flysystem\\Filesystem', [new NullAdapter()]);
        static::$filesystemHandle->readStream->returns(null);
        static::$filesystemHandle->getMimetype->returns('test');
        static::$filesystemHandle->getSize->returns(42);

        $userSetup = new UserSetup();
        $userSetup->addEvents($service->getData('user'));

        return $service;
    }

    public static function getFileProcessor()
    {
        return self::$fileProcessor;
    }

}
