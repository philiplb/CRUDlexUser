<?php
namespace CRUDlexUserTestEnv;

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

use CRUDlex\MySQLDataFactory;
use CRUDlex\ServiceProvider;
use CRUDlex\UserSetup;
use CRUDlexUserTestEnv\NullFileProcessor;

class TestDBSetup {

    private static $fileProcessor;

    public static function createAppAndDB() {
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
            '  `password` varchar(255) NOT NULL,'.
            '  `salt` varchar(255) NOT NULL,'.
            '  `username` varchar(255) NOT NULL,'.
            '  `email` varchar(255) NOT NULL,'.
            '  PRIMARY KEY (`id`)'.
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $app['db']->executeUpdate($sql);

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

    public static function createServiceProvider() {
        self::$fileProcessor = new NullFileProcessor();
        $app = self::createAppAndDB();
        $crudServiceProvider = new ServiceProvider();
        $dataFactory = new MySQLDataFactory($app['db']);
        $crudFile = __DIR__.'/../crud.yml';
        $crudServiceProvider->init($dataFactory, $crudFile, self::$fileProcessor, true, $app);

        $userSetup = new UserSetup();
        $userSetup->addEvents($crudServiceProvider->getData('user'));

        return $crudServiceProvider;
    }

    public static function getFileProcessor() {
        return self::$fileProcessor;
    }

}
