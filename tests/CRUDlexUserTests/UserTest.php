<?php

/*
 * This file is part of the CRUDlexUser package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlexUserTests;

use CRUDlex\Entity;
use CRUDlex\User;
use CRUDlexUserTestEnv\TestDBSetup;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    private $userEntity;

    public function __construct() {
        $crudServiceProvider = TestDBSetup::createServiceProvider(false);
        $dataUser = $crudServiceProvider->getData('user');
        $this->userEntity = $dataUser->createEmpty();
        $this->userEntity->set('username', 'username');
        $this->userEntity->set('password', 'password');
        $this->userEntity->set('salt', 'salt');
    }

    public function testGetRoles() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $read = $user->getRoles();
        $this->assertSame($read, $roles);
    }

    public function testGetPassword() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $expected = 'password';
        $read = $user->getPassword();
        $this->assertSame($read, $expected);
    }

    public function testGetSalt() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $expected = 'salt';
        $read = $user->getSalt();
        $this->assertSame($read, $expected);
    }

    public function testGetUsername() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $expected = 'username';
        $read = $user->getUsername();
        $this->assertSame($read, $expected);
    }

    public function testEraseCredentials() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $user->eraseCredentials();
    }

    public function testGetUserData() {
        $roles = ['ROLE_TEST'];
        $user = new User('username', 'password', 'salt', $this->userEntity, $roles);
        $expected = 'username';
        $read = $user->getUserData();
        $this->assertSame($read['username'], $expected);
    }

}
