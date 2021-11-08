<?php

declare(strict_types=1);
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

namespace Shopware\Recovery\Update;

class UpdateHtaccess
{
    private const TEMPLATE_SUFFIX = '.dist';
    private const MARKER_START = '# BEGIN Shopware';
    private const MARKER_STOP = '# END Shopware';
    private const INSTRUCTIONS = '# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.';

    private string $htaccessPath;

    /**
     * This constant contains the hashes of known previous states of the
     * .htaccess file. Modifications done by the SwagSecurity plugin v1.1.25 and
     * v1.1.26 have been taken into account aswell. If we encounter one of these
     * versions, the file gets replaced with the new .htacces.dist.
     */
    private const OLD_FILES = [
        'ea1cf343d866211a2cd3fb1cda96c568', // https://github.com/shopware/shopware/blob/2822875087/.htaccess
        '50201598e7180f7c9165936a9c5f4a53', // https://github.com/shopware/shopware/blob/2822875087/.htaccess + SwagSecurity section
        'ae5381e3a07011f35a45c066749f76a5', // https://github.com/shopware/shopware/blob/37213e91d5/.htaccess
        'baeb964cec3d2696e1030a5018b345c6', // https://github.com/shopware/shopware/blob/37213e91d5/.htaccess + SwagSecurity section
        'e18138d4ef5c4dda6c5a24f796d83109', // https://github.com/shopware/shopware/blob/f307af9286/.htaccess
        'c0f0bea10c3a8f5b1550d8d537294e32', // https://github.com/shopware/shopware/blob/f307af9286/.htaccess + SwagSecurity section
        'f45b5cf94584b362960acca19bd79a02', // https://github.com/shopware/shopware/blob/e4b24110b9/.htaccess
        'fd449fd74ce36aa3f4f01be5b198c42b', // https://github.com/shopware/shopware/blob/e4b24110b9/.htaccess + SwagSecurity section
        '9d8825b4597f8f46dc40137b7afe2602', // https://github.com/shopware/shopware/blob/dea3a35b71/.htaccess
        '7dc562c8f584358dae85e335e192c59f', // https://github.com/shopware/shopware/blob/dea3a35b71/.htaccess + SwagSecurity section
        '657f2a28ce414c1a859f9f104bf16522', // https://github.com/shopware/shopware/blob/649b5a6e0a/.htaccess
        'a10d145e79029b78c797e58995eb2743', // https://github.com/shopware/shopware/blob/649b5a6e0a/.htaccess + SwagSecurity section
        'ece5e7d151499f18db260955642bc342', // https://github.com/shopware/shopware/blob/1982fda855/.htaccess
        'bece28210fc7c53c424999daa2f93303', // https://github.com/shopware/shopware/blob/1982fda855/.htaccess + SwagSecurity section
        'ebe05d01541cfd3bb4dcb4f46b761c0f', // https://github.com/shopware/shopware/blob/24b7abaa25/.htaccess
        '16147aac5b54351c55f1cc2403892f40', // https://github.com/shopware/shopware/blob/24b7abaa25/.htaccess + SwagSecurity section
        '54085d04dbcf32bdc27a464258380240', // https://github.com/shopware/shopware/blob/cf8907c6c5/.htaccess
        '78f7207131fad361a710991f73620dd6', // https://github.com/shopware/shopware/blob/cf8907c6c5/.htaccess + SwagSecurity section
        'be8e8ddf9e048dcd127beeb7a1f2b93b', // https://github.com/shopware/shopware/blob/053632e79f/.htaccess
        'aaa3dae669543f176ec76ef1acb53b1b', // https://github.com/shopware/shopware/blob/053632e79f/.htaccess + SwagSecurity section
        '33dd62e280adb9809173c3208eb14547', // https://github.com/shopware/shopware/blob/b021d77c8a/.htaccess
        '7707cd5927b638a258548fd78c31e61a', // https://github.com/shopware/shopware/blob/b021d77c8a/.htaccess + SwagSecurity section
        '45ef3f6ca5788085a3bb7378a1510c80', // https://github.com/shopware/shopware/blob/4aba9b79ac/.htaccess
        '79b3663dd1dfa883a6d23b7cc6f56bd0', // https://github.com/shopware/shopware/blob/4aba9b79ac/.htaccess + SwagSecurity section
        '486f52a32a31b97e11d5a83a0add7541', // https://github.com/shopware/shopware/blob/c0616709fb/.htaccess
        'ec269ec668c9e64c6e188ee0bdf9c273', // https://github.com/shopware/shopware/blob/c0616709fb/.htaccess + SwagSecurity section
        '49959e6a8fa61bf38b1c269e97da0d46', // https://github.com/shopware/shopware/blob/2ab66c45c9/.htaccess
        'afde28bbcdf4f0d437ec44641a6faa3c', // https://github.com/shopware/shopware/blob/2ab66c45c9/.htaccess + SwagSecurity section
        '1927450e0bcd5a1fbdad879356329f9d', // https://github.com/shopware/shopware/blob/0c0f6ff2e6/.htaccess
        '7df38ad7aaa2410e363b3945759d9cf4', // https://github.com/shopware/shopware/blob/0c0f6ff2e6/.htaccess + SwagSecurity section
        '136f3597e8745ae57e7e3139fab84c78', // https://github.com/shopware/shopware/blob/710076a2a2/.htaccess
        'b3ece266f62b8f7ab75307f2e9734605', // https://github.com/shopware/shopware/blob/710076a2a2/.htaccess + SwagSecurity section
        'b7f41355e1db0103b48bfe36458e4bb7', // https://github.com/shopware/shopware/blob/bb95afbd01/.htaccess
        '596eb625c7f2804c10ec7be1ab441c82', // https://github.com/shopware/shopware/blob/bb95afbd01/.htaccess + SwagSecurity section
        '781a1cdbd51aabf58e4626b3db8fd9c5', // https://github.com/shopware/shopware/blob/08661660a6/.htaccess
        'aaff6dcc929190c7f65e26fe3ec0538b', // https://github.com/shopware/shopware/blob/08661660a6/.htaccess + SwagSecurity section
        '5d7e6211ff581e12cbd9769e5b3da4bd', // https://github.com/shopware/shopware/blob/7662e140d2/.htaccess
        '7ca5b55b89c5a2306307d9b65479791a', // https://github.com/shopware/shopware/blob/7662e140d2/.htaccess + SwagSecurity section
    ];

    public function __construct(string $htaccessPath)
    {
        $this->htaccessPath = $htaccessPath;
    }

    public function update(): void
    {
        if (!file_exists($this->htaccessPath) || !file_exists($this->htaccessPath . self::TEMPLATE_SUFFIX)) {
            return;
        }

        if (\in_array(md5_file($this->htaccessPath), self::OLD_FILES, true)) {
            $this->replaceFile($this->htaccessPath);

            return;
        }

        $content = file_get_contents($this->htaccessPath);

        // User has deleted the markers. So we will ignore the update process
        if (strpos($content, self::MARKER_START) === false || strpos($content, self::MARKER_STOP) === false) {
            return;
        }

        $this->updateByMarkers($this->htaccessPath);
    }

    /**
     * Replace entire .htaccess from dist
     */
    private function replaceFile(string $path): void
    {
        $dist = $path . self::TEMPLATE_SUFFIX;

        if (!file_exists($dist)) {
            return;
        }

        $perms = fileperms($dist);
        copy($dist, $path);

        if ($perms) {
            chmod($path, $perms | 0644);
        }
    }

    private function updateByMarkers(string $path): void
    {
        [$pre, $_, $post] = $this->getLinesFromMarkedFile($path);
        [$_, $existing, $_] = $this->getLinesFromMarkedFile($path . self::TEMPLATE_SUFFIX);

        if (!\in_array(self::INSTRUCTIONS, $existing, true)) {
            array_unshift($existing, self::INSTRUCTIONS);
        }

        array_unshift($existing, self::MARKER_START);
        $existing[] = self::MARKER_STOP;

        $newFile = implode("\n", array_merge($pre, $existing, $post));

        $perms = fileperms($path);
        file_put_contents($path, $newFile);

        if ($perms) {
            chmod($path, $perms | 0644);
        }
    }

    private function getLinesFromMarkedFile(string $path): array
    {
        $fp = fopen($path, 'rb+');
        if (!$fp) {
            return [];
        }

        $lines = [];
        while (!feof($fp)) {
            if ($line = fgets($fp)) {
                $lines[] = rtrim($line, "\r\n");
            }
        }

        $foundStart = false;
        $foundStop = false;
        $preLines = [];
        $postLines = [];
        $existingLines = [];

        foreach ($lines as $line) {
            if (!$foundStart && strpos($line, self::MARKER_START) === 0) {
                $foundStart = true;

                continue;
            }

            if (!$foundStop && strpos($line, self::MARKER_STOP) === 0) {
                $foundStop = true;

                continue;
            }

            if (!$foundStart) {
                $preLines[] = $line;
            } elseif ($foundStop) {
                $postLines[] = $line;
            } else {
                $existingLines[] = $line;
            }
        }

        return [$preLines, $existingLines, $postLines];
    }
}
