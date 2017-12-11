<?php
/**
 * This Software is part of aryelgois/composer-front-end and is provided "as is"
 *
 * @see LICENSE
 */

namespace aryelgois\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * A utility to install front-end files with Composer
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/composer-front-end
 */
class FrontEnd
{
    /**
     * Path to package root
     *
     * @var string
     */
    protected static $root;

    /**
     * Data in frontend-config.json
     *
     * @var array
     */
    protected static $config;

    /**
     * If should replace an already existing link
     *
     * @var boolean
     */
    protected static $replace;

    /**
     * Installs front-end files from package just installed
     *
     * @param PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        self::setEnvironment();

        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');

        $package = $event->getOperation()->getPackage();
        $package_dir = $vendor_dir . '/' . $package->getName();

        self::symlink($package_dir);
    }

    /**
     * Installs front-end files for every package installed
     *
     * @param Event $event
     */
    public static function refresh(Event $event)
    {
        self::setEnvironment();

        self::$replace = true;

        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');
        foreach (glob($vendor_dir . '/*/*', GLOB_ONLYDIR) as $package_dir) {
            self::symlink($package_dir);
        }
    }

    /**
     * Prepares some common variables
     */
    protected static function setEnvironment()
    {
        self::$root = getcwd();

        $config = json_decode(file_get_contents(self::$root . '/frontend-config.json'), true);
        if ($config === null) {
            echo "Fatal: Could not load frontend-config.json\n";
            die(1);
        }

        self::$config = $config;
    }

    /**
     * Creates symlinks for files in the package
     */
    protected static function symlink($package_dir)
    {
        $package = basename(dirname($package_dir)) . '/' . basename($package_dir);
        $local_file = false;
        $directories = self::$config['directories'] ?? [];
        $structure_default = self::$config['structure_default'] ?? 'nest';

        if (array_key_exists($package, self::$config['packages'] ?? [])) {
            $files = self::$config['packages'][$package];
        } else {
            $files_path = $package_dir . '/frontend.json';
            if (file_exists($files_path)) {
                $files = json_decode(file_get_contents($files_path), true);
                if ($files === null) {
                    echo "Warning: Error loading frontend.json from '" . $package . "'\n";
                    return;
                }
                $local_file = true;
            }
        }

        if (!isset($files)) {
            return;
        }

        foreach ($files as $group => $file_paths) {
            if (!array_key_exists($group, $directories)) {
                echo "Warning: Unknown group '" . $group .
                     "' for '" . $package .
                     "' in frontend" . ($local_file ? '' : '-config') . ".json\n";
                continue;
            }

            $structure = self::$config['structure'][$group] ?? $structure_default;
            switch ($structure) {
                case 'flat':
                    $prefix = '';
                    break;

                case 'nest':
                default:
                    $prefix = '/' . $package;
                    break;
            }

            $dir = self::$root . '/' . $directories[$group] . $prefix;
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            foreach ((array) $file_paths as $file) {
                $target = realpath($package_dir . '/' . $file);
                $link = $dir . '/' . basename($file);
                if (file_exists($link)) {
                    if (self::$replace) {
                        unlink($link);
                    } else {
                        echo "Warning: File '" . $link . "' already exists\n";
                        continue;
                    }
                }
                symlink($target, $link);
            }
        }
    }
}
