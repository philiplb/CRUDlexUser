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

use CRUDlex\CRUDUser;

class CRUDEntityTest extends \PHPUnit_Framework_TestCase {

    public function testGetRoles() {
        $expected = array('ROLE_TEST');
        $user = new CRUDUser(null, null, null, $expected);
        $read = $user->getRoles();
        $this->assertSame($read, $expected);
    }

    public function testGetPassword() {
        $expected = 'password';
        $user = new CRUDUser(null, $expected, null, array());
        $read = $user->getPassword();
        $this->assertSame($read, $expected);
    }

    public function testGetSalt() {
        $expected = 'salt';
        $user = new CRUDUser(null, null, $expected, array());
        $read = $user->getSalt();
        $this->assertSame($read, $expected);
    }

    public function testGetUsername() {
        $expected = 'username';
        $user = new CRUDUser($expected, null, null, array());
        $read = $user->getUsername();
        $this->assertSame($read, $expected);
    }

    public function testEraseCredentials() {
        $user = new CRUDUser(null, null, null, array());
        $user->eraseCredentials();
    }

}