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

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use CRUDlex\CRUDUserProvider;

use CRUDlexUserTestEnv\CRUDTestDBSetup;

class CRUDUserProviderTest extends \PHPUnit_Framework_TestCase {

    protected $crudServiceProvider;

    protected $dataUser;

    protected $dataRole;

    protected $dataUserRole;

    protected function setUp() {
        $this->crudServiceProvider = CRUDTestDBSetup::createCRUDServiceProvider();
        $this->dataUser = $this->crudServiceProvider->getData('user');
        $this->dataRole = $this->crudServiceProvider->getData('role');
        $this->dataUserRole = $this->crudServiceProvider->getData('userRole');
    }

    public function testLoadUserByUsername() {

        $expected = 'user1';
        $user = $this->dataUser->createEmpty();
        $user->set('username', $expected);
        $user->set('password', 'asdasd');
        $this->dataUser->create($user);

        $role = $this->dataRole->createEmpty();
        $role->set('role', 'ROLE_TEST');
        $this->dataRole->create($role);

        $userRole = $this->dataUserRole->createEmpty();
        $userRole->set('user', $user->get('id'));
        $userRole->set('role', $role->get('id'));
        $this->dataUserRole->create($userRole);

        $userProvider = new CRUDUserProvider($this->dataUser, $this->dataUserRole);

        $userRead = $userProvider->loadUserByUsername($expected);
        $read = $userRead->getUsername();
        $this->assertSame($read, $expected);
        $read = $userRead->getRoles();
        $expected = array('ROLE_USER', 'ROLE_TEST');
        $this->assertSame($read, $expected);

        try {
            $read = $userProvider->loadUserByUsername('foo');
            $this->fail();
        } catch (UsernameNotFoundException $e) {
            // Expected.
        }
    }

    public function testRefreshUser() {

        $expected = 'user1';
        $user = $this->dataUser->createEmpty();
        $user->set('username', $expected);
        $user->set('password', 'asdasd');
        $this->dataUser->create($user);

        $userProvider = new CRUDUserProvider($this->dataUser, $this->dataUserRole);
        $userRead = $userProvider->loadUserByUsername($expected);

        $expected = array('ROLE_USER');
        $read = $userRead->getRoles();
        $this->assertSame($read, $expected);

        $role = $this->dataRole->createEmpty();
        $role->set('role', 'ROLE_TEST');
        $this->dataRole->create($role);

        $userRole = $this->dataUserRole->createEmpty();
        $userRole->set('user', $user->get('id'));
        $userRole->set('role', $role->get('id'));
        $this->dataUserRole->create($userRole);

        $userRead2 = $userProvider->refreshUser($userRead);
        $expected = array('ROLE_USER', 'ROLE_TEST');
        $read = $userRead2->getRoles();
        $this->assertSame($read, $expected);

    }

    public function testSupportsClass() {
        $userProvider = new CRUDUserProvider($this->dataUser, $this->dataUserRole);
        $read = $userProvider->supportsClass('CRUDlex\CRUDUser');
        $this->assertTrue($read);
        $read = $userProvider->supportsClass('foo');
        $this->assertFalse($read);
        $read = $userProvider->supportsClass(null);
        $this->assertFalse($read);
    }

}