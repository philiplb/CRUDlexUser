<?php

/*
 * This file is part of the CRUDlexUser package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

date_default_timezone_set('Europe/Berlin');
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('CRUDlexUserTestEnv', __DIR__);
$loader->add('CRUDlexUserTests', __DIR__);
