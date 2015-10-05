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

use CRUDlex\CRUDData;

/**
 * This class setups CRUDlex with some events so the passwords get salted and
 * hashed properly.
 */
class CRUDUserSetup {


	/**
	 * Generates a random salt of the given length.
	 */
	protected function getSalt($len) {
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
	 * @param CRUDData $data
	 * the CRUDData instance managing the users
	 *
     * @param string $passwordField
     * the CRUDEntity fieldname of the password hash
	 *
     * @param string $saltField
     * the CRUDEntity fieldname of the password hash salt
	 */
    public function addEvents(CRUDData $data, $passwordField = 'password', $saltField = 'salt') {

        $saltGenFunction = function(CRUDEntity $entity) use ($saltField) {
            $salt = $this->getSalt(40);
            $entity->set($saltField, $salt);
            return true;
        };

        $data->pushEvent('before', 'create', $saltGenFunction);

        $pwHashFunction = function(CRUDEntity $entity) use ($data, $passwordField, $saltField) {
            $password = $entity->get($passwordField);

			if (!$password) {
				return true;
			}

            $encoder = new MessageDigestPasswordEncoder();
            $salt = $entity->get($saltField);

			if (!$salt) {
	            $salt = $this->getSalt(40);
	            $entity->set($saltField, $salt);
			}

			$passwordHash = $encoder->encodePassword($password, $salt);

			$doGenerateHash = true;
			$id = $entity->get('id');
			if ($id !== null) {
				$oldEntity = $data->get($entity->get('id'));
				$doGenerateHash = $oldEntity->get($passwordField) !== $password;
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
