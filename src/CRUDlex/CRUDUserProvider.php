<?php

/*
 * This file is part of the CRUDlexUser package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlex;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use CRUDlex\CRUDData;
use CRUDlex\CRUDUser;

/**
 * The implementation of the UserProviderInterface to work with the CRUDlex API.
 */
class CRUDUserProvider implements UserProviderInterface {

    /**
     * The CRUDEntity fieldname of the username.
     */
    protected $usernameField;

    /**
     * The fieldname of the password (hash).
     */
    protected $passwordField;

    /**
     * The fieldname of the password hash salt.
     */
    protected $saltField;

    /**
     * Holds the CRUDData instance to grab the user data from.
     */
    protected $userData;

    /**
     * Holds the CRUDData instance to grab the user role data from.
     */
    protected $userRoleData;

    /**
     * Constructor.
     *
     * @param CRUDData $userData
     * the CRUDData instance to grab the user data from
     *
     * @param CRUDData $userRoleData
     * the CRUDData instance to grab the user role data from
     *
     * @param string $usernameField
     * the CRUDEntity fieldname of the username
     *
     * @param string $passwordField
     * the CRUDEntity fieldname of the password hash
     *
     * @param string $saltField
     * the CRUDEntity fieldname of the password hash salt
     */
    public function __construct(CRUDData $userData, CRUDData $userRoleData, $usernameField = 'username', $passwordField = 'password', $saltField = 'salt') {
        $this->userData = $userData;
        $this->userRoleData = $userRoleData;
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
        $this->saltField = $saltField;
    }

    /**
     * Loads and returns an user by username.
     * Throws an UsernameNotFoundException on not existing username.
     *
     * @param string $username
     * the username
     *
     * @return CRUDUser
     * the loaded user
     */
    public function loadUserByUsername($username) {

        $crudUsers = $this->userData->listEntries(array($this->usernameField => $username), array($this->usernameField => '='), 0, 1);
        if (count($crudUsers) === 0) {
            throw new UsernameNotFoundException();
        }

        $crudUser = $crudUsers[0];
        $password = $crudUser->get($this->passwordField);
        $salt = $crudUser->get($this->saltField);

        $crudRoles = $this->userRoleData->listEntries(array('user' => $crudUser->get('id')), array('user' => '='));
        $this->userRoleData->fetchReferences($crudRoles);
        $roles = array('ROLE_USER');
        foreach ($crudRoles as $crudRole) {
            $role = $crudRole->get('role');
            $roles[] = $role['name'];
        }

        $user = new CRUDUser($username, $password, $salt, $roles);

        return $user;
    }

    /**
     * Reloads and returns the given user.
     * Throws an UsernameNotFoundException if the user ceased to exist meanwhile.
     *
     * @param UserInterface $user
     * the user to reload
     *
     * @return CRUDUser
     * the reloaded user
     */
    public function refreshUser(UserInterface $user) {
        $refreshedUser = $this->loadUserByUsername($user->getUsername());
        return $refreshedUser;
    }

    /**
     * Tests whether the given user class is supported by this UserProvider.
     *
     * @param string $class
     * the user class name to test
     *
     * @return boolean
     * true if the class is "CRUDlex\CRUDUser"
     */
    public function supportsClass($class) {
        return $class === 'CRUDlex\CRUDUser';
    }

}
