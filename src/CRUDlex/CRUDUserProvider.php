<?php

namespace CRUDlex;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use CRUDlex\CRUDData;
use CRUDlex\CRUDUser;

class CRUDUserProvider implements UserProviderInterface {

    protected $passwordField;

    protected $saltField;

    protected $userData;

    protected $userRoleData;

    public function __construct(CRUDData $userData, CRUDData $userRoleData, $usernameField = 'username', $passwordField = 'password', $saltField = 'salt') {
        $this->userData = $userData;
        $this->userRoleData = $userRoleData;
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
        $this->saltField = $saltField;
    }

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

    public function refreshUser(UserInterface $user) {
        $refreshedUser = $this->loadUserByUsername($user->getUsername());
        return $refreshedUser;
    }

    public function supportsClass($class) {
        return $class === 'CRUDUser';
    }

}
