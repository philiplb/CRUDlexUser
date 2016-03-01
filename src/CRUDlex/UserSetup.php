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

/**
 * This class setups CRUDlex with some events so the passwords get salted and
 * hashed properly.
 */
class UserSetup {


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

        $pwHashFunction = function(Entity $entity) use ($data, $passwordField, $saltField, $that) {
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

            $doGenerateHash = true;
            $id = $entity->get('id');
            if ($id !== null) {
                $oldEntity = $data->get($entity->get('id'));
                $doGenerateHash = $oldEntity->get($passwordField) !== $password || $newSalt;
            }

            if ($doGenerateHash) {
                $entity->set($passwordField, $passwordHash);
            }
            return true;
        };

        $data->pushEvent('before', 'create', $pwHashFunction);
        $data->pushEvent('before', 'update', $pwHashFunction);

    }

}
