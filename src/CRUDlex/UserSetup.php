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

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use CRUDlex\AbstractData;
use CRUDlex\Entity;

/**
 * This class setups CRUDlex with some events so the passwords get salted and
 * hashed properly.
 */
class UserSetup
{

    /**
     * The encoder to use.
     */
    protected $encoder;

    /**
     * Gets a closure for possibly generating a password hash in the entity.
     *
     * @param AbstractData $data
     * the AbstractData instance managing the users
     *
     * @param string $passwordField
     * the Entity fieldname of the password hash
     *
     * @param string $saltField
     * the Entity fieldname of the password hash salt
     */
    protected function getPWHashFunction(AbstractData $data, $passwordField, $saltField)
    {
        $that = $this;
        return function(Entity $entity) use ($data, $passwordField, $saltField, $that) {
            $password = $entity->get($passwordField);

            if (!$password) {
                return true;
            }

            $salt = $entity->get($saltField);
            $newSalt = $that->possibleGenSalt($salt, $entity, $saltField);

            $passwordHash = $this->encoder->encodePassword($password, $salt);

            $doGenerateHash = $that->doGenerateHash($data, $entity, $passwordField, $password, $newSalt);

            if ($doGenerateHash) {
                $entity->set($passwordField, $passwordHash);
            }
            return true;
        };
    }

    /**
     * Constructor.
     *
     * @param PasswordEncoderInterface $encoder
     * the encoder to use, defaults to BCryptPasswordEncoder if null is given
     */
    public function __construct(PasswordEncoderInterface $encoder = null)
    {
        $this->encoder = $encoder;
        if ($this->encoder === null) {
            $this->encoder = new BCryptPasswordEncoder(13);
        }
    }

    /**
     * Generates a new salt if the given salt is null.
     *
     * @param string $salt
     * the salt to override if null
     * @param Entity
     * the entity getting the new salt
     * @param string $saltField
     * the field holding the salt in the entity
     *
     * @return boolean
     * true if a new salt was generated
     */
    public function possibleGenSalt(&$salt, Entity $entity, $saltField)
    {
        if (!$salt) {
            $salt = $this->getSalt(40);
            $entity->set($saltField, $salt);
            return true;
        }
        return false;
    }

    /**
     * Determines whether the entity needs a new hash generated.
     *
     * @param AbstractData $data
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
    public function doGenerateHash(AbstractData $data, Entity $entity, $passwordField, $password, $newSalt)
    {
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
    public function getSalt($len)
    {
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
     * @param AbstractData $data
     * the AbstractData instance managing the users
     *
     * @param string $passwordField
     * the Entity fieldname of the password hash
     *
     * @param string $saltField
     * the Entity fieldname of the password hash salt
     */
    public function addEvents(AbstractData $data, $passwordField = 'password', $saltField = 'salt')
    {

        $that = $this;
        $saltGenFunction = function(Entity $entity) use ($saltField, $that) {
            $salt = $that->getSalt(40);
            $entity->set($saltField, $salt);
            return true;
        };

        $data->getEvents()->push('before', 'create', $saltGenFunction);

        $pwHashFunction = $this->getPWHashFunction($data, $passwordField, $saltField);

        $data->getEvents()->push('before', 'create', $pwHashFunction);
        $data->getEvents()->push('before', 'update', $pwHashFunction);

    }

}
