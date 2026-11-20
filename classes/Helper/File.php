<?php
/**
 * @author Greenrivers
 * @copyright Copyright (c) 2022 Greenrivers
 * @package Grav\Plugin\Webp
 */

namespace Grav\Plugin\Webp\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class File
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /** @var Filesystem */
    private $filesystem;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename): bool
    {
        return $this->filesystem->exists($filename);
    }

    /**
     * Returns the image at the given path, as long as it is a jpg/jpeg/png/webp file inside the Grav root.
     *
     * @param string $filename path relative to the Grav root, or an absolute path inside it
     * @return SplFileInfo|null
     */
    public function getFile(string $filename): ?SplFileInfo
    {
        $filename = $this->stringStartsWith($filename, ROOT_DIR) ? $filename : ROOT_DIR . $filename;

        // Resolve both paths, so that "..", symlinks and mixed separators cannot point outside the Grav root.
        $root = realpath(ROOT_DIR);
        $pathname = realpath($filename);

        if (
            $root === false ||
            $pathname === false ||
            !is_file($pathname) ||
            !$this->stringStartsWith($pathname, $root . DIRECTORY_SEPARATOR)
        ) {
            return null;
        }

        $relativePathname = substr($pathname, strlen($root) + 1);
        $relativePath = dirname($relativePathname);

        $file = new SplFileInfo($pathname, $relativePath === '.' ? '' : $relativePath, $relativePathname);

        return in_array($file->getExtension(), self::IMAGE_EXTENSIONS, true) ? $file : null;
    }

    /**
     * @param string $text
     * @param string $startsWith
     * @return bool
     */
    private function stringStartsWith(string $text, string $startsWith): bool
    {
        return strpos($text, $startsWith) === 0;
    }
}
