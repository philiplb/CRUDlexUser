<?php

namespace CRUDlex;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

use CRUDlex\CRUDData;

class CRUDUserSetup {


	protected function getSalt($len) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()-=_+';
		$l = strlen($chars) - 1;
		$str = '';
		for ($i = 0; $i < $len; ++$i) {
			$str .= $chars[mt_rand(0, $l)];
		}
		return $str;
	}

    public function addEvents(CRUDData $data, $passwordField = 'password', $saltField = 'salt') {

        $saltGenFunction = function(CRUDEntity $entity) use ($saltField) {
            $salt = $this->getSalt(40);
            $entity->set($saltField, $salt);
            return true;
        };

        $data->pushEvent('before', 'create', $saltGenFunction);

        $pwHashFunction = function(CRUDEntity $entity) use ($data, $passwordField, $saltField) {
            $password = $entity->get($passwordField);
            $encoder = new MessageDigestPasswordEncoder();
            $salt = $entity->get($saltField);
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
