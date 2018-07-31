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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use CRUDlex\UserProvider;

use CRUDlexUserTestEnv\TestDBSetup;

class UserProviderTest extends TestCase
{

    protected $service;

    protected $dataUser;

    protected $dataRole;

    protected $dataUserRole;

    protected function setUp()
    {
        $this->service = TestDBSetup::createService(false);
        $this->dataUser = $this->service->getData('user');
        $this->dataRole = $this->service->getData('role');
        $this->dataUserRole = $this->service->getData('userRole');
    }

    public function testLoadUserByUsername()
    {

        $expected = 'user1';
        $user = $this->dataUser->createEmpty();
        $user->set('username', $expected);
        $user->set('password', 'asdasd');
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);

        $role = $this->dataRole->createEmpty();
        $role->set('role', 'ROLE_TEST');
        $this->dataRole->create($role);

        $userRole = $this->dataUserRole->createEmpty();
        $userRole->set('user', $user->get('id'));
        $userRole->set('role', $role->get('id'));
        $this->dataUserRole->create($userRole);

        $userProvider = new UserProvider($this->service, 'user', $this->dataUserRole);

        $userRead = $userProvider->loadUserByUsername($expected);
        $read = $userRead->getUsername();
        $this->assertSame($read, $expected);
        $read = $userRead->getRoles();
        $expected = ['ROLE_USER', 'ROLE_TEST'];
        $this->assertSame($read, $expected);

        try {
            $userProvider->loadUserByUsername('foo');
            $this->fail();
        } catch (UsernameNotFoundException $e) {
            // Expected.
        }
    }

    public function testRefreshUser()
    {

        $expected = 'user1';
        $user = $this->dataUser->createEmpty();
        $user->set('username', $expected);
        $user->set('password', 'asdasd');
        $user->set('email', 'asd@asd.de');
        $this->dataUser->create($user);

        $userProvider = new UserProvider($this->service, 'user', $this->dataUserRole);
        $userRead = $userProvider->loadUserByUsername($expected);

        $expected = ['ROLE_USER'];
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
        $expected = ['ROLE_USER', 'ROLE_TEST'];
        $read = $userRead2->getRoles();
        $this->assertSame($read, $expected);

    }

    public function testSupportsClass()
    {
        $userProvider = new UserProvider($this->service, 'user', $this->dataUserRole);
        $read = $userProvider->supportsClass('CRUDlex\User');
        $this->assertTrue($read);
        $read = $userProvider->supportsClass('foo');
        $this->assertFalse($read);
        $read = $userProvider->supportsClass(null);
        $this->assertFalse($read);
    }

    public function testRolesViaManyToMany()
    {
        $crudService = TestDBSetup::createService(true);
        $this->dataUser = $crudService->getData('user');
        $userProvider = new UserProvider($crudService, 'user', 'user_role');

        $role = $this->dataRole->createEmpty();
        $role->set('role', 'ROLE_TEST');
        $this->dataRole->create($role);

        $expected = 'user1';
        $user = $this->dataUser->createEmpty();
        $user->set('username', $expected);
        $user->set('password', 'asdasd');
        $user->set('email', 'asd@asd.de');
        $user->set('user_role', [['id' => $role->get('id')]]);
        $this->dataUser->create($user);
        $userRead = $userProvider->loadUserByUsername($expected);
        $read = $userRead->getUsername();
        $this->assertSame($read, $expected);
        $read = $userRead->getRoles();
        $expected = ['ROLE_USER', 'ROLE_TEST'];
        $this->assertSame($read, $expected);
    }

}
