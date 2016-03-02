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

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use CRUDlex\Data;
use CRUDlex\Entity;

/**
 * This class setups CRUDlex with some events so the passwords get salted and
 * hashed properly.
 */
class UserSetup {

    /**
     * Gets a closure for possibly generating a password hash in the entity.
     *
     * @param Data $data
     * the Data instance managing the users
     *
     * @param string $passwordField
     * the Entity fieldname of the password hash
     *
     * @param string $saltField
     * the Entity fieldname of the password hash salt
     */
    protected function getPWHashFunction(Data $data, $passwordField, $saltField) {
        $that = $this;
        return function(Entity $entity) use ($data, $passwordField, $saltField, $that) {
            $password = $entity->get($passwordField);

            if (!$password) {
                return true;
            }

            $encoder = new MessageDigestPasswordEncoder();
            $salt = $entity->get($saltField);
            $newSalt = false;
            if (!$salt) {
                $salt = $that->getSalt(40);
                $entity->set($saltField, $salt);
                $newSalt = true;
            }

            $passwordHash = $encoder->encodePassword($password, $salt);

            $doGenerateHash = $that->doGenerateHash($data, $entity, $passwordField, $password, $newSalt);

            if ($doGenerateHash) {
                $entity->set($passwordField, $passwordHash);
            }
            return true;
        };
    }

    /**
     * Determines whether the entity needs a new hash generated.
     *
     * @param Data $data
     * the CRUDlex data instance of the user entity
     * @param Entity $entity
     * the entity
     * @param string $passwordField
     * the field holding the password hash in the entity
     * @param string $password
     * the current password hash
     * @param boolean $newSalt
     * whether a new password hash salt was generated
     *
     * @return boolean
     * true if the entity needs a new hash
     */
    public function doGenerateHash(Data $data, Entity $entity, $passwordField, $password, $newSalt) {
        $doGenerateHash = true;
        $id = $entity->get('id');
        if ($id !== null) {
            $oldEntity = $data->get($entity->get('id'));
            $doGenerateHash = $oldEntity->get($passwordField) !== $password || $newSalt;
        }
        return $doGenerateHash;
    }

    /**
     * Generates a random salt of the given length.
     *
     * @param int $len
     * the desired length
     *
     * @return string
     * a random salt of the given length
     */
    public function getSalt($len) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
        $l = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i) {
            $str .= $chars[mt_rand(0, $l)];
        }
        return $str;
    }

    /**
     * Setups CRUDlex with some events so the passwords get salted and
     * hashed properly.
     *
     * @param Data $data
     * the Data instance managing the users
     *
     * @param string $passwordField
     * the Entity fieldname of the password hash
     *
     * @param string $saltField
     * the Entity fieldname of the password hash salt
     */
    public function addEvents(Data $data, $passwordField = 'password', $saltField = 'salt') {

        $that = $this;
        $saltGenFunction = function(Entity $entity) use ($saltField, $that) {
            $salt = $that->getSalt(40);
            $entity->set($saltField, $salt);
            return true;
        };

        $data->pushEvent('before', 'create', $saltGenFunction);

        $pwHashFunction = $this->getPWHashFunction($data, $passwordField, $saltField);

        $data->pushEvent('before', 'create', $pwHashFunction);
        $data->pushEvent('before', 'update', $pwHashFunction);

    }

}
