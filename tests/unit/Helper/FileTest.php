<?php
/**
 * @author Greenrivers
 * @copyright Copyright (c) 2022 Greenrivers
 * @package Grav\Plugin\Webp
 */

namespace Tests\Unit\Helper;

use Codeception\Test\Unit;
use Grav\Plugin\Webp\Helper\File;
use Symfony\Component\Filesystem\Filesystem;

class FileTest extends Unit
{
    /** @var File */
    private $file;

    /**
     * @inheritDoc
     */
    protected function _before()
    {
        // Grav defines ROOT_DIR as the installation root with a trailing slash.
        if (!defined('ROOT_DIR')) {
            $tmp = str_replace('\\', '/', sys_get_temp_dir());
            define('ROOT_DIR', $tmp . '/webp-plugin-tests-' . getmypid() . '/grav/');
        }

        mkdir(ROOT_DIR . 'user/images/folder.jpg', 0777, true);
        touch(ROOT_DIR . 'logo.png');
        touch(ROOT_DIR . 'user/images/photo.jpg');
        touch(ROOT_DIR . 'user/images/photo.webp');
        touch(ROOT_DIR . 'user/images/upper.JPG');
        touch(ROOT_DIR . 'user/images/notes.txt');
        touch(dirname(ROOT_DIR) . '/outside.jpg');

        $this->file = new File();
    }

    /**
     * @inheritDoc
     */
    protected function _after()
    {
        (new Filesystem())->remove(dirname(ROOT_DIR));
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileByRelativePath()
    {
        $result = $this->file->getFile('user/images/photo.jpg');

        $this->assertNotNull($result);
        $this->assertEquals('jpg', $result->getExtension());
        $this->assertEquals('photo', $result->getFilenameWithoutExtension());
        $this->assertEquals('user' . DIRECTORY_SEPARATOR . 'images', $result->getRelativePath());
        $this->assertEquals(
            'user' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'photo.jpg',
            $result->getRelativePathname()
        );
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileByAbsolutePath()
    {
        $result = $this->file->getFile(ROOT_DIR . 'user/images/photo.webp');

        $this->assertNotNull($result);
        $this->assertEquals('webp', $result->getExtension());
        $this->assertEquals(
            'user' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'photo.webp',
            $result->getRelativePathname()
        );
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileFromRootDirectory()
    {
        $result = $this->file->getFile('logo.png');

        $this->assertNotNull($result);
        $this->assertEquals('', $result->getRelativePath());
        $this->assertEquals('logo.png', $result->getRelativePathname());
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileOutsideRootDirectory()
    {
        $this->assertFileExists(ROOT_DIR . '../outside.jpg');

        $this->assertNull($this->file->getFile('../outside.jpg'));
        $this->assertNull($this->file->getFile('user/images/../../../outside.jpg'));
        $this->assertNull($this->file->getFile(ROOT_DIR . '../outside.jpg'));
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileWithUnsupportedExtension()
    {
        $this->assertNull($this->file->getFile('user/images/notes.txt'));
        $this->assertNull($this->file->getFile('user/images/upper.JPG'));
    }

    /**
     * @covers File::getFile
     */
    public function testGetFileIgnoresDirectoriesAndMissingFiles()
    {
        $this->assertNull($this->file->getFile('user/images'));
        $this->assertNull($this->file->getFile('user/images/folder.jpg'));
        $this->assertNull($this->file->getFile('user/images/missing.jpg'));
    }
}
