<?php

/*
 * This file is part of the CRUDlexUser package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlexUserTestEnv;

use CRUDlex\UserProvider;

use CRUDlex\UserSetup;

use CRUDlexUserTestEnv\TestDBSetup;

class UserSetupTest extends \PHPUnit_Framework_TestCase {

    protected $dataUser;

    protected function setUp() {
        $crudServiceProvider = TestDBSetup::createServiceProvider();
        $this->dataUser = $crudServiceProvider->getData('user');
    }

    public function testAddEvents() {
        $password = 'asdasd';
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user1');
        $user->set('password', $password);
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);

        $readUser = $this->dataUser->get($user->get('id'));
        $hash = $readUser->get('password');
        $this->assertNotSame($password, $hash);

        $salt = $readUser->get('salt');
        $this->assertNotEmpty($salt);

        $user->set('password', 'dsadsa');
        $this->dataUser->update($user);

        $readUser2 = $this->dataUser->get($user->get('id'));
        $read = $readUser2->get('salt');
        $this->assertSame($salt, $read);

        $read = $readUser2->get('password');
        $this->assertNotSame($read, $hash);

    }

    public function testGetSalt() {
        $userSetup = new UserSetup();
        $read = $userSetup->getSalt(40);
        $this->assertTrue(strlen($read) === 40);
    }
}
