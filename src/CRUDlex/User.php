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

use Symfony\Component\Security\Core\User\UserInterface;
use CRUDlex\Entity;

/**
 * The UserInterface implementation for the UserProvider.
 */
class User implements UserInterface
{

    /**
     * Holds the actual user data.
     */
    private $userData;

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
     * Hold password hash salt.
     */
    private $roles;

    /**
     * Constructor.
     *
     * @param string $usernameField
     * the username
     *
     * @param string $passwordField
     * the password (hash)
     *
     * @param string $saltField
     * the password hash salt
     *
     * @param Entity $userEntity
     * the actual user data
     *
     * @param array $roles
     * the roles
     */
    public function __construct($usernameField, $passwordField, $saltField, Entity $userEntity, array $roles)
    {
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
        $this->saltField = $saltField;
        // We have to copy it over as symfony/security wants something serializable.
        $this->userData = [];
        foreach ($userEntity->getDefinition()->getFieldNames() as $field) {
            $this->userData[$field] = $userEntity->get($field);
        }
        $this->roles = $roles;
    }

    /**
     * Gets the roles.
     *
     * @return array
     * the roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Gets the password (hash).
     *
     * @return string
     * the password
     */
    public function getPassword()
    {
        return $this->userData[$this->passwordField];
    }

    /**
     * Gets the password hash salt.
     *
     * @return string
     * the salt
     */
    public function getSalt()
    {
        return $this->userData[$this->saltField];
    }

    /**
     * Gets the username.
     *
     * @return string
     * the username
     */
    public function getUsername()
    {
        return $this->userData[$this->usernameField];
    }

    /**
     * Should erase some crucial data if needed. But nothing to do here in this
     * implementation.
     */
    public function eraseCredentials()
    {
    }

    /**
     * Gets the user data.
     *
     * @return array
     * the user data
     */
    public function getUserData()
    {
        return $this->userData;
    }

}
