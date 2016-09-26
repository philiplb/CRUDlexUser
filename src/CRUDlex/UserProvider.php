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
use CRUDlex\AbstractData;
use CRUDlex\User;

/**
 * The implementation of the UserProviderInterface to work with the CRUDlex API.
 */
class UserProvider implements UserProviderInterface {

    /**
     * The Entity fieldname of the username.
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
     * Holds the AbstractData instance to grab the user data from.
     */
    protected $userData;

    /**
     * Holds the AbstractData instance or the field of the many-to-many relationship to grab the user role data from.
     */
    protected $userRoleData;

    /**
     * Holds the AbstractData instance or the field of the many-to-many relationship to grab the user role data from.
     */
    protected $userRoleIdentifier;

    /**
     * Loads the roles of an user via an AbstractData instance.
     *
     * @param mixed $userId
     * the id of the user
     *
     * @return string[]
     * the roles of the user
     */
    protected function loadUserRolesViaData($userId) {
        $crudRoles = $this->userRoleIdentifier->listEntries(['user' => $userId], ['user' => '=']);
        $this->userRoleIdentifier->fetchReferences($crudRoles);
        $roles = ['ROLE_USER'];
        if ($crudRoles !== null) {
            foreach ($crudRoles as $crudRole) {
                $role = $crudRole->get('role');
                $roles[] = $role['name'];
            }
        }
        return $roles;
    }

    /**
     * Loads the roles of an user via a many-to-many relationship
     *
     * @param Entity $user
     * the id of the user
     *
     * @return string[]
     * the roles of the user
     */
    protected function loadUserRolesViaManyToMany($user) {
        $roles = ['ROLE_USER'];
        foreach ($user->get($this->userRoleIdentifier) as $role) {
            $roles[] = $role['name'];
        }
        return $roles;
    }

    /**
     * Constructor for data structures connecting users and roles via a many-to-many relationship on the user.
     *
     * @param AbstractData $userData
     * the AbstractData instance to grab the user data from
     *
     * @param string|AbstractData $userRoleIdentifier
     * the field of the many-to-many relationship to grab the user role data from or the AbstractData if its an own entity
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
    public function __construct(AbstractData $userData, $userRoleIdentifier = 'roles', $usernameField = 'username', $passwordField = 'password', $saltField = 'salt') {
        $this->userData = $userData;
        $this->userRoleIdentifier = $userRoleIdentifier;
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

        $users = $this->userData->listEntries([$this->usernameField => $username], [$this->usernameField => '='], 0, 1);
        if (count($users) === 0) {
            throw new UsernameNotFoundException();
        }

        $user = $users[0];
        $roles = is_string($this->userRoleIdentifier) ? $this->loadUserRolesViaManyToMany($user) : $this->loadUserRolesViaData($user->get('id'));

        $userObj = new User($this->usernameField, $this->passwordField, $this->saltField, $user, $roles);
        return $userObj;
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
