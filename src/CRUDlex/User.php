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

/**
 * The UserInterface implementation for the UserProvider.
 */
class User implements UserInterface {

    /**
     * Hold the username.
     */
    private $username;

    /**
     * Hold the password.
     */
    private $password;

    /**
     * Hold the roles.
     */
    private $salt;

    /**
     * Hold password hash salt.
     */
    private $roles;

    /**
     * Constructor.
     *
     * @param string $username
     * the username
     *
     * @param string $password
     * the password (hash)
     *
     * @param string $salt
     * the password hash salt
     *
     * @param array $roles
     * the roles
     */
    public function __construct($username, $password, $salt, array $roles) {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    /**
     * Gets the roles.
     *
     * @return array
     * the roles
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * Gets the password (hash).
     *
     * @return string
     * the password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Gets the password hash salt.
     *
     * @return string
     * the salt
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * Gets the username.
     *
     * @return string
     * the username
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Should erase some crucial data if needed. But nothing to do here in this
     * implementation.
     */
    public function eraseCredentials() {
    }

}
