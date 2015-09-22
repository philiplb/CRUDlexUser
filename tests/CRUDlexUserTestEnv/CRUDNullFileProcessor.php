<?php

/*
 * This file is part of the CRUDlexUser package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlexUserTestEnv;

use CRUDlex\CRUDFileProcessorInterface;
use CRUDlex\CRUDEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CRUDNullFileProcessor implements CRUDFileProcessorInterface {

    public function __construct() {
    }

    public function createFile(Request $request, CRUDEntity $entity, $entityName, $field) {
    }

    public function updateFile(Request $request, CRUDEntity $entity, $entityName, $field) {
    }

    public function deleteFile(CRUDEntity $entity, $entityName, $field) {
    }

    public function renderFile(CRUDEntity $entity, $entityName, $field) {
        return '';
    }

}
