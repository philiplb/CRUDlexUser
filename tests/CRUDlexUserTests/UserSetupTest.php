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
use PHPUnit\Framework\TestCase;

class UserSetupTest extends TestCase
{

    protected $dataUser;

    protected function setUp()
    {
        $crudServiceProvider = TestDBSetup::createService(false);
        $this->dataUser = $crudServiceProvider->getData('user');
    }

    public function testAddEvents()
    {
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


        $password = '';
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user2');
        $user->set('password', $password);
        $user->set('email', 'asd2@asd2.de');
        $this->dataUser->create($user);
        $readUser = $this->dataUser->get($user->get('id'));
        $hash = $readUser->get('password');
        $this->assertEmpty($hash);

    }

    public function testGetSalt()
    {
        $userSetup = new UserSetup();
        $read = $userSetup->getSalt(40);
        $this->assertTrue(strlen($read) === 40);
    }

    public function testPossibleGenSalt()
    {
        $password = 'asdasd';
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user1');
        $user->set('password', $password);
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);
        $userSetup = new UserSetup();

        $salt = 'test';
        $read = $userSetup->possibleGenSalt($salt, $user, 'salt');
        $this->assertFalse($read);

        $salt = null;
        $read = $userSetup->possibleGenSalt($salt, $user, 'salt');
        $this->assertTrue($read);
        $read = $user->get('salt');
        $this->assertNotEmpty($read);
    }
}
