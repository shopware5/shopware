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

namespace ShopwarePlugins\SwagUpdate\Components;

class Download
{
    /**
     * @var callable
     */
    private $progressCallback;

    /**
     * @var callable
     */
    private $haltCallback;

    /**
     * @param callable $callback
     *
     * @throws \Exception
     */
    public function setProgressCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Callback not callable');
        }

        $this->progressCallback = $callback;
    }

    /**
     * @param callable $callback
     *
     * @throws \Exception
     */
    public function setHaltCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Callback not callable');
        }

        $this->haltCallback = $callback;
    }

    /**
     * @return bool
     */
    public function shouldHalt()
    {
        if (!$this->haltCallback) {
            return false;
        }

        return (bool) call_user_func($this->haltCallback);
    }

    /**
     * @param string $sourceUri
     * @param string $destinationUri
     * @param int    $totalSize
     * @param string $hash
     *
     * @throws \Exception
     *
     * @return int
     */
    public function downloadFile($sourceUri, $destinationUri, $totalSize, $hash)
    {
        if (($destination = fopen($destinationUri, 'a+')) === false) {
            throw new \Exception(sprintf('Destination "%s" is invalid.', $destinationUri));
        }

        if (filesize($destinationUri) > 0) {
            throw new \Exception(sprintf('File on destination %s does already exist.', $destinationUri));
        }

        $partFile = $destinationUri . '.part';
        $partFile = new \SplFileObject($partFile, 'a+');

        $size = $partFile->getSize();
        if ($size >= $totalSize) {
            $this->verifyHash($partFile, $hash);
            // close local file connections before move for windows
            $partFilePath = $partFile->getPathname();
            fclose($destination);
            unset($partFile);
            $this->moveFile($partFilePath, $destinationUri);

            return 0;
        }

        $range = $size . '-' . ($totalSize - 1);

        if (!function_exists('curl_init')) {
            throw new \Exception('PHP Extension "curl" is required to download a file');
        }

        // Configuration of curl
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RANGE, $range);
        curl_setopt($ch, CURLOPT_URL, $sourceUri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);

        $me = $this;
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($ch, $dltotal, $dlnow) use ($me, $size) {
            if ($dlnow > 0) {
                $this->progress($dltotal, $dlnow, $size + $dlnow);
            }
        });

        $me = $this;

        $isHalted = false;
        $isError = false;
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) use ($me, $partFile, &$isHalted, &$isError) {
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 206) {
                $isError = true;

                return -1;
            }

            $partFile->fwrite($str);

            if ($me->shouldHalt()) {
                $isHalted = true;

                return -1;
            }

            return strlen($str);
        });

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($isError && !$isHalted) {
            throw new \Exception('Wrong http code');
        }

        if ($result === false && !$isHalted) {
            throw new \Exception($error);
        }

        clearstatcache(false, $partFile->getPathname());
        $size = $partFile->getSize();

        if ($size >= $totalSize) {
            $this->verifyHash($partFile, $hash);
            // close local file connections before move for windows
            $partFilePath = $partFile->getPathname();
            fclose($destination);
            unset($partFile);
            $this->moveFile($partFilePath, $destinationUri);
        }

        // close local file
        fclose($destination);
        unset($partFile);

        return $size;
    }

    /**
     * @param int $downloadSize
     * @param int $downloaded
     * @param int $total
     */
    private function progress($downloadSize, $downloaded, $total)
    {
        if (!$this->progressCallback) {
            return;
        }

        call_user_func_array($this->progressCallback, [$downloadSize, $downloaded, $total]);
    }

    /**
     * @param \SplFileObject $partFile
     * @param string         $hash
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function verifyHash($partFile, $hash)
    {
        if (sha1_file($partFile->getPathname()) !== $hash) {
            // try to delete invalid file so a valid one can be downloaded
            @unlink($partFile->getPathname());
            throw new \Exception('Hash mismatch');
        }

        return true;
    }

    /**
     * @param string $partFilePath
     * @param string $destinationUri
     */
    private function moveFile($partFilePath, $destinationUri)
    {
        rename($partFilePath, $destinationUri);
    }
}
