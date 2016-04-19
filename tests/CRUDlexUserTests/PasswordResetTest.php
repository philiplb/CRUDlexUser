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

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
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

        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user1');
        $user->set('password', 'asdasd');
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

    public function testResetPassword() {
        $app = TestDBSetup::createAppAndDB();
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user2');
        $user->set('password', 'asdasd');
        $user->set('email', 'asd2@asd.de');
        $this->dataUser->create($user);

        $oldHash = $user->get('password');
        $salt = $user->get('salt');

        $encoder = new MessageDigestPasswordEncoder();
        $passwordHash = $encoder->encodePassword('asdasd', $salt);
        $this->assertSame($passwordHash, $oldHash);

        $token = $this->passwordReset->requestPasswordReset('email', 'asd2@asd.de');

        $read = $this->passwordReset->resetPassword('asdasd', 'dsadsa');
        $this->assertFalse($read);
        $read = $this->passwordReset->resetPassword('', 'dsadsa');
        $this->assertFalse($read);
        $read = $this->passwordReset->resetPassword(null, 'dsadsa');
        $this->assertFalse($read);

        $read = $this->passwordReset->resetPassword($token, 'dsadsa');
        $this->assertTrue($read);

        $updatedUser = $this->dataUser->get($user->get('id'));
        $newHash = $updatedUser->get('password');
        $passwordHash = $encoder->encodePassword('dsadsa', $salt);
        $this->assertSame($passwordHash, $newHash);

        // A token can be only used once
        $read = $this->passwordReset->resetPassword($token, 'dsadsa');
        $this->assertFalse($read);

        // A password reset must be used within 48h
        $token = $this->passwordReset->requestPasswordReset('email', 'asd2@asd.de');
        $passwordResets = $this->dataPasswordReset->listEntries(array('token' => $token));
        if (count($passwordResets) !== 1) {
            $this->fail();
        }
        $passwordReset = $passwordResets[0];
        $oldCreatedAt = gmdate('Y-m-d H:i:s', time() - 3 * 24 * 60 * 60);
        $app['db']->executeUpdate('UPDATE password_reset SET created_at = ? WHERE token = ?', array($oldCreatedAt, $token));

        $read = $this->passwordReset->resetPassword($token, 'dsadsa');
        $this->assertFalse($read);

    }

}
