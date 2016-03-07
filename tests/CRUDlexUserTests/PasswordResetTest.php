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

use CRUDlex\PasswordReset;
use CRUDlexUserTestEnv\TestDBSetup;

class PasswordResetTest extends \PHPUnit_Framework_TestCase {

    private $dataUser;

    private $dataPasswordReset;

    private $passwordReset;

    public function __construct() {
        $crudServiceProvider = TestDBSetup::createServiceProvider();
        $this->dataUser = $crudServiceProvider->getData('user');
        $this->dataPasswordReset = $crudServiceProvider->getData('passwordReset');
        $this->passwordReset = new PasswordReset($this->dataUser, $this->dataPasswordReset);
    }

    public function testRequestPasswordReset() {

        $password = 'asdasd';
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user1');
        $user->set('password', $password);
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);

        $read = $this->passwordReset->requestPasswordReset('email', 'dsa@dsa.de');
        $this->assertNull($read);
        $read = $this->passwordReset->requestPasswordReset('email', '');
        $this->assertNull($read);
        $read = $this->passwordReset->requestPasswordReset('email', null);
        $this->assertNull($read);

        $token = $this->passwordReset->requestPasswordReset('email', 'asd@asd.de');
        $this->assertTrue(strlen($token) === 32);

        $read = $this->dataPasswordReset->countBy($this->dataPasswordReset->getDefinition()->getTable(), array('token' => $token), array('token' => '='), true);
        $expected = 1;
        $this->assertSame($read, $expected);

    }

}
