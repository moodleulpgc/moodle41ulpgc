<?php
/**
 * This file is part of the SetaPDF package
 *
 * @copyright  Copyright (c) 2020 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @package    SetaPDF
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Autoload.php 1417 2020-02-03 18:04:25Z jan.slabon $
 */

spl_autoload_register(static function ($class) {
    static $path = null;

    if (strpos($class, 'SetaPDF_') === 0) {
        if ($path === null) {
            $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        }

        $filename = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        $fullpath = $path . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($fullpath)) {
            /** @noinspection PhpIncludeInspection */
            require_once $fullpath;
        }
    }
});