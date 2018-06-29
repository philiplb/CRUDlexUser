<?php
namespace CRUDlexUserTestEnv;

use League\Flysystem\Adapter\NullAdapter;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

use Eloquent\Phony\Phpunit\Phony;

use CRUDlex\MySQLDataFactory;
use CRUDlex\ServiceProvider;
use CRUDlex\UserSetup;
use CRUDlexUserTestEnv\NullFileProcessor;

class TestDBSetup {

    private static $filesystemHandle;

    public static function createAppAndDB($useManyToMany)
    {
        $app = new Application();
        $app->register(new DoctrineServiceProvider(), [
            'dbs.options' => [
                'default' => [
                    'host'      => '127.0.0.1',
                    'dbname'    => 'crudTest',
                    'user'      => 'root',
                    'password'  => '',
                    'charset'   => 'utf8',
                ]
            ],
        ]);

        $app['db']->executeUpdate('DROP TABLE IF EXISTS password_reset;');
        $app['db']->executeUpdate('DROP TABLE IF EXISTS user_role;');
        $app['db']->executeUpdate('DROP TABLE IF EXISTS role;');
        $app['db']->executeUpdate('DROP TABLE IF EXISTS user;');

        $app['db']->executeUpdate('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
        $app['db']->executeUpdate('SET time_zone = "+00:00"');

        $sql = 'CREATE TABLE `role` ('.
            '  `id` int(11) NOT NULL AUTO_INCREMENT,'.
            '  `version` int(11) NOT NULL,'.
            '  `created_at` datetime NOT NULL,'.
            '  `updated_at` datetime NOT NULL,'.
            '  `deleted_at` datetime DEFAULT NULL,'.
            '  `role` varchar(255) NOT NULL,'.
            '  PRIMARY KEY (`id`)'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $app['db']->executeUpdate($sql);

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
        $app['db']->executeUpdate($sql);

        if ($useManyToMany) {
            $sql = 'CREATE TABLE `user_role` ('.
                '  `user` int(11) NOT NULL,'.
                '  `role` int(11) NOT NULL,'.
                '  KEY `user` (`user`),'.
                '  KEY `role` (`role`),'.
                '  CONSTRAINT `userrole_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`),'.
                '  CONSTRAINT `userrole_ibfk_2` FOREIGN KEY (`role`) REFERENCES `role` (`id`)'.
                ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $app['db']->executeUpdate($sql);
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
            $app['db']->executeUpdate($sql);
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
        $app['db']->executeUpdate($sql);
        return $app;
    }

    public static function createServiceProvider($useManyToMany)
    {

        static::$filesystemHandle = Phony::partialMock('\\League\\Flysystem\\Filesystem', [new NullAdapter()]);
        static::$filesystemHandle->readStream->returns(null);
        static::$filesystemHandle->getMimetype->returns('test');
        static::$filesystemHandle->getSize->returns(42);

        $app = self::createAppAndDB($useManyToMany);
        $crudServiceProvider = new ServiceProvider();

        $app['crud.filesystem'] = static::$filesystemHandle->get();
        $app['crud.datafactory'] = new MySQLDataFactory($app['db']);
        $app['crud.file'] = __DIR__.'/../'.($useManyToMany ? 'crudManyToMany.yml' : 'crud.yml');
        $crudServiceProvider->boot($app);
        $crudServiceProvider->init(null, $app);

        $userSetup = new UserSetup();
        $userSetup->addEvents($crudServiceProvider->getData('user'));

        return $crudServiceProvider;
    }

    public static function getFileProcessor()
    {
        return self::$fileProcessor;
    }

}
