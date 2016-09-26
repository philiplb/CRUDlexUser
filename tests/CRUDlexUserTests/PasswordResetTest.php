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

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use CRUDlex\PasswordReset;
use CRUDlexUserTestEnv\TestDBSetup;

class PasswordResetTest extends \PHPUnit_Framework_TestCase {

    private $dataUser;

    private $dataPasswordReset;

    public function __construct() {
        $crudServiceProvider = TestDBSetup::createServiceProvider(false);
        $this->dataUser = $crudServiceProvider->getData('user');
        $this->dataPasswordReset = $crudServiceProvider->getData('passwordReset');
    }

    public function testRequestPasswordReset() {
        $passwordReset = new PasswordReset($this->dataUser, $this->dataPasswordReset);

        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user1');
        $user->set('password', 'asdasd');
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);

        $read = $passwordReset->requestPasswordReset('email', 'dsa@dsa.de');
        $this->assertNull($read);
        $read = $passwordReset->requestPasswordReset('email', '');
        $this->assertNull($read);
        $read = $passwordReset->requestPasswordReset('email', null);
        $this->assertNull($read);

        $token = $passwordReset->requestPasswordReset('email', 'asd@asd.de');
        $this->assertTrue(strlen($token) === 32);

        $read = $this->dataPasswordReset->countBy($this->dataPasswordReset->getDefinition()->getTable(), ['token' => $token], ['token' => '='], true);
        $expected = 1;
        $this->assertSame($read, $expected);

    }

    public function testResetPassword() {
        $passwordReset = new PasswordReset($this->dataUser, $this->dataPasswordReset);
        $app = TestDBSetup::createAppAndDB(false);
        $user = $this->dataUser->createEmpty();
        $user->set('username', 'user2');
        $user->set('password', 'asdasd');
        $user->set('email', 'asd2@asd.de');
        $this->dataUser->create($user);

        $hash = $user->get('password');
        $salt = $user->get('salt');

        $encoder = new BCryptPasswordEncoder(13);
        $this->assertTrue($encoder->isPasswordValid($hash, 'asdasd', $salt));

        $token = $passwordReset->requestPasswordReset('email', 'asd2@asd.de');

        $read = $passwordReset->resetPassword('asdasd', 'dsadsa');
        $this->assertFalse($read);
        $read = $passwordReset->resetPassword('', 'dsadsa');
        $this->assertFalse($read);
        $read = $passwordReset->resetPassword(null, 'dsadsa');
        $this->assertFalse($read);

        $read = $passwordReset->resetPassword($token, 'dsadsa');
        $this->assertTrue($read);

        $updatedUser = $this->dataUser->get($user->get('id'));
        $newHash = $updatedUser->get('password');
        $this->assertTrue($encoder->isPasswordValid($newHash, 'dsadsa', $salt));

        // A token can be only used once
        $read = $passwordReset->resetPassword($token, 'dsadsa');
        $this->assertFalse($read);

        // A password reset must be used within 48h
        $token = $passwordReset->requestPasswordReset('email', 'asd2@asd.de');
        $passwordResets = $this->dataPasswordReset->listEntries(['token' => $token]);
        if (count($passwordResets) !== 1) {
            $this->fail();
        }
        $oldCreatedAt = gmdate('Y-m-d H:i:s', time() - 3 * 24 * 60 * 60);
        $app['db']->executeUpdate('UPDATE password_reset SET created_at = ? WHERE token = ?', [$oldCreatedAt, $token]);

        $read = $passwordReset->resetPassword($token, 'dsadsa');
        $this->assertFalse($read);

    }

}
