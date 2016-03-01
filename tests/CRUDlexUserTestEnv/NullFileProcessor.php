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

use CRUDlex\FileProcessorInterface;
use CRUDlex\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NullFileProcessor implements FileProcessorInterface {

    public function __construct() {
    }

    public function createFile(Request $request, Entity $entity, $entityName, $field) {
    }

    public function updateFile(Request $request, Entity $entity, $entityName, $field) {
    }

    public function deleteFile(Entity $entity, $entityName, $field) {
    }

    public function renderFile(Entity $entity, $entityName, $field) {
        return '';
    }

}
