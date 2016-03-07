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

use CRUDlex\UserSetup;

/**
 * This class offers some features to implement a password reset flow.
 */
class PasswordReset {

    /**
     * Holds the user Data instance.
     */
    protected $userData;

    /**
     * Holds the password reset Data instance.
     */
    protected $passwordResetData;

    /**
     * Constructor.
     *
     * @param CRUDlex\Data $userData
     * the user data instance
     * @param CRUDlex\Data $passwordResetData
     * the password reset data instance
     */
    public function __construct($userData, $passwordResetData) {
        $this->userData = $userData;
        $this->passwordResetData = $passwordResetData;
    }

    /**
     * Creates a password reset request.
     *
     * @param string $identifyingField
     * the identifying field to grab an user, likely the email
     * @param string $identifyingValue
     * the identifying value to grab an user, likely the email
     *
     * @return null|string
     * the token of the password reset instance ready to be send to the user via
     * a secondary channel like email; might be null if the user could not be
     * identified uniquly via the given parameters: either zero or more than one
     * users were found
     */
    public function requestPasswordReset($identifyingField, $identifyingValue) {

        $users = $this->userData->listEntries(array($identifyingField => $identifyingValue));
        if (count($users) !== 1) {
            return null;
        }

        $user = $users[0];
        $userSetup = new UserSetup();

        do {
            $token = $userSetup->getSalt(32);
            $tokenFound = $this->passwordResetData->countBy($this->passwordResetData->getDefinition()->getTable(), array('token' => $token), array('token' => '='), true) === 0;
        } while (!$tokenFound);

        $passwordReset = $this->passwordResetData->createEmpty();
        $passwordReset->set('user', $user->get('id'));
        $passwordReset->set('token', $token);
        $this->passwordResetData->create($passwordReset);

        return $token;
    }

    /**
     * Resets the password of an user belonging to the given password reset
     * token.
     *
     * @param string $token
     * the password reset token
     * @param string $newPassword
     * the new password
     *
     * @return boolean
     * true on success, false on failure with one of this reasons:
     * - no or more than one password reset request found for this token
     * - the password request for this token is older than 48h
     * - the password request for this token has already been used
     */
    public function resetPassword($token, $newPassword) {

        $passwordResets = $this->passwordResetData->listEntries(array('token' => $token));
        if (count($passwordResets) !== 1) {
            return false;
        }
        $passwordReset = $passwordResets[0];

        $createdAt = $passwordReset->get('created_at');
        if (strtotime($createdAt.' UTC') < time() - 2 * 24 * 60 * 60) {
            return false;
        }

        if ($passwordReset->get('reset')) {
            return false;
        }

        $user = $this->userData->get($passwordReset->get('user'));
        $user->set('password', $newPassword);
        $this->userData->update($user);

        $reset = gmdate('Y-m-d H:i:s');
        $passwordReset->set('reset', $reset);
        $this->passwordResetData->update($passwordReset);

        return true;
    }

}
