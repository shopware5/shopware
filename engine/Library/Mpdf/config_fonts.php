<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

// Deactivated since Shopware 4.0.1 at least
unset($this->backupSIPFont);

$this->fonttrans = [
    'helvetica' => 'arial',
    'verdana' => 'arial',
    'times' => 'timesnewroman',
    'courier' => 'couriernew',
    'trebuchet' => 'arial',
    'comic' => 'arial',
    'franklin' => 'arial',
    'albertus' => 'arial',
    'arialuni' => 'arial',
    'zn_hannom_a' => 'arial',
    'ocr-b' => 'ocrb',
    'ocr-b10bt' => 'ocrb',
    'damase' => 'mph2bdamase',
];

$this->fontdata = [
    'arial' => [
        'R' => 'arial.ttf',
        'B' => 'arialbd.ttf',
        'I' => 'ariali.ttf',
        'BI' => 'arialbi.ttf',
    ],
    'couriernew' => [
        'R' => 'cour.ttf',
        'B' => 'courbd.ttf',
        'I' => 'couri.ttf',
        'BI' => 'courbi.ttf',
    ],
    'georgia' => [
        'R' => 'georgia.ttf',
        'B' => 'georgiab.ttf',
        'I' => 'georgiai.ttf',
        'BI' => 'georgiaz.ttf',
    ],
    'timesnewroman' => [
        'R' => 'times.ttf',
        'B' => 'timesbd.ttf',
        'I' => 'timesi.ttf',
        'BI' => 'timesbi.ttf',
    ],
    'verdana' => [
        'R' => 'verdana.ttf',
        'B' => 'verdanab.ttf',
        'I' => 'verdanai.ttf',
        'BI' => 'verdanaz.ttf',
    ],
];
