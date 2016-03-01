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
use CRUDlex\Data;
use CRUDlex\User;

/**
 * The implementation of the UserProviderInterface to work with the CRUDlex API.
 */
class UserProvider implements UserProviderInterface {

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
     * @param Data $userData
     * the Data instance to grab the user data from
     *
     * @param Data $userRoleData
     * the Data instance to grab the user role data from
     *
     * @param string $usernameField
     * the Entity fieldname of the username
     *
     * @param string $passwordField
     * the Entity fieldname of the password hash
     *
     * @param string $saltField
     * the Entity fieldname of the password hash salt
     */
    public function __construct(Data $userData, Data $userRoleData, $usernameField = 'username', $passwordField = 'password', $saltField = 'salt') {
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
     * @return User
     * the loaded user
     */
    public function loadUserByUsername($username) {

        $Users = $this->userData->listEntries(array($this->usernameField => $username), array($this->usernameField => '='), 0, 1);
        if (count($Users) === 0) {
            throw new UsernameNotFoundException();
        }

        $User = $Users[0];
        $password = $User->get($this->passwordField);
        $salt = $User->get($this->saltField);

        $crudRoles = $this->userRoleData->listEntries(array('user' => $User->get('id')), array('user' => '='));
        $this->userRoleData->fetchReferences($crudRoles);
        $roles = array('ROLE_USER');
        foreach ($crudRoles as $crudRole) {
            $role = $crudRole->get('role');
            $roles[] = $role['name'];
        }

        $user = new User($username, $password, $salt, $roles);

        return $user;
    }

    /**
     * Reloads and returns the given user.
     * Throws an UsernameNotFoundException if the user ceased to exist meanwhile.
     *
     * @param UserInterface $user
     * the user to reload
     *
     * @return User
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
     * true if the class is "CRUDlex\User"
     */
    public function supportsClass($class) {
        return $class === 'CRUDlex\User';
    }

}
