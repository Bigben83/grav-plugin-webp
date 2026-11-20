<?php
/**
 * @author Greenrivers
 * @copyright Copyright (c) 2022 Greenrivers
 * @package Grav\Plugin\Webp
 */

namespace Tests\Unit\Helper;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Grav\Plugin\Webp\Helper\Image;
use Symfony\Component\Finder\SplFileInfo;

class ImageTest extends Unit
{
    /** @var Image */
    private $image;

    /** @var string[] */
    private $tempImages = [];

    /**
     * @inheritDoc
     */
    protected function _before()
    {
        $this->image = new Image();
    }

    /**
     * @covers Image::getImageData
     */
    public function testGetImageData()
    {
        $imageMock = $this->make(SplFileInfo::class, [
            'getExtension' => Expected::once('jpg'),
            'getPathname' => Expected::once('user/themes/quark/thumbnail.jpg'),
            'getPathInfo' => Expected::once(
                $this->make(SplFileInfo::class, [
                    'getPathName' => Expected::once('user/themes/quark')
                ])
            ),
            'getFilenameWithoutExtension' => Expected::once('thumbnail')
        ]);

        $result = $this->image->getImageData($imageMock);

        $this->assertArrayHasKey('extension', $result);
        $this->assertArrayHasKey('pathname', $result);
        $this->assertArrayHasKey('pathinfo', $result);
        $this->assertArrayHasKey('pathname', $result['pathinfo']);
        $this->assertArrayHasKey('filenamewithoutextension', $result);

        $this->assertEquals('jpg', $result['extension']);
        $this->assertEquals('user/themes/quark/thumbnail.jpg', $result['pathname']);
        $this->assertEquals('user/themes/quark', $result['pathinfo']['pathname']);
        $this->assertEquals('thumbnail', $result['filenamewithoutextension']);
    }

    /**
     * @covers Image::getImageData
     */
    public function testGetImageDataFromCli()
    {
        $imageMock = $this->make(SplFileInfo::class, [
            'getExtension' => Expected::once('jpg'),
            'getRelativePathname' => Expected::once('user/themes/quark/thumbnail.jpg'),
            'getRelativePath' => Expected::once('user/themes/quark'),
            'getFilenameWithoutExtension' => Expected::once('thumbnail')
        ]);

        $result = $this->image->getImageData($imageMock, true);

        $this->assertArrayHasKey('extension', $result);
        $this->assertArrayHasKey('pathname', $result);
        $this->assertArrayHasKey('pathinfo', $result);
        $this->assertArrayHasKey('pathname', $result['pathinfo']);
        $this->assertArrayHasKey('filenamewithoutextension', $result);

        $this->assertEquals('jpg', $result['extension']);
        $this->assertEquals('user/themes/quark/thumbnail.jpg', $result['pathname']);
        $this->assertEquals('user/themes/quark', $result['pathinfo']['pathname']);
        $this->assertEquals('thumbnail', $result['filenamewithoutextension']);
    }

    /**
     * @covers Image::getWebpPath
     */
    public function testGetWebpPath()
    {
        $originalPath = false;
        $pathname = 'user/themes/quark';
        $filenameWithoutExtension = 'thumbnail';

        $result = $this->image->getWebpPath($originalPath, $pathname, $filenameWithoutExtension);

        $this->assertEquals(
            'user/webp' . DIRECTORY_SEPARATOR . 'user/themes/quark' . DIRECTORY_SEPARATOR . 'thumbnail.webp',
            $result
        );
    }

    /**
     * @covers Image::getWebpPath
     */
    public function testOriginalGetWebpPath()
    {
        $originalPath = true;
        $pathname = 'user/themes/quark';
        $filenameWithoutExtension = 'thumbnail';

        $result = $this->image->getWebpPath($originalPath, $pathname, $filenameWithoutExtension);

        $this->assertEquals('user/themes/quark' . DIRECTORY_SEPARATOR . 'thumbnail.webp', $result);
    }

    /**
     * @covers Image::getWebpDir
     */
    public function testGetWebpDir()
    {
        $pathname = 'user/themes/quark';

        $result = $this->image->getWebpDir($pathname);

        $this->assertEquals('user/webp' . DIRECTORY_SEPARATOR . 'user/themes/quark', $result);
    }

    /**
     * @covers Image::adjustImageOrientation
     */
    public function testAdjustRotatedImageOrientation()
    {
        $imagePath = $this->createImageWithOrientation(6);

        Image::adjustImageOrientation($imagePath);

        // Rotated by 90 degrees: the size is swapped and the red (left) half ends up at the top.
        $this->assertEquals([20, 40], array_slice(getimagesize($imagePath), 0, 2));
        $this->assertTrue($this->isRed($imagePath, 2, 2));
        $this->assertFalse($this->isRed($imagePath, 2, 37));
    }

    /**
     * @covers Image::adjustImageOrientation
     */
    public function testAdjustMirroredImageOrientation()
    {
        $imagePath = $this->createImageWithOrientation(2);

        Image::adjustImageOrientation($imagePath);

        // Mirrored horizontally: the red (left) half ends up on the right.
        $this->assertEquals([40, 20], array_slice(getimagesize($imagePath), 0, 2));
        $this->assertFalse($this->isRed($imagePath, 2, 2));
        $this->assertTrue($this->isRed($imagePath, 37, 2));
    }

    /**
     * @inheritDoc
     */
    protected function _after()
    {
        array_map('unlink', $this->tempImages);
        $this->tempImages = [];
    }

    /**
     * Creates a 40x20 jpg (left half red, right half blue) with the given EXIF orientation.
     *
     * @param int $orientation
     * @return string
     */
    private function createImageWithOrientation(int $orientation): string
    {
        $image = imagecreatetruecolor(40, 20);
        imagefilledrectangle($image, 0, 0, 19, 19, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 20, 0, 39, 19, imagecolorallocate($image, 0, 0, 255));

        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();

        // Minimal EXIF segment: a little-endian TIFF header and one IFD holding the Orientation tag (0x0112).
        $tiff = "II*\0" . pack('V', 8) . pack('v', 1) . pack('vvVvv', 0x0112, 3, 1, $orientation, 0) . pack('V', 0);
        $exif = "Exif\0\0" . $tiff;
        $segment = "\xFF\xE1" . pack('n', strlen($exif) + 2) . $exif;

        $imagePath = tempnam(sys_get_temp_dir(), 'webp') . '.jpg';
        // The EXIF segment goes right after the SOI marker (first two bytes).
        file_put_contents($imagePath, substr($jpeg, 0, 2) . $segment . substr($jpeg, 2));
        $this->tempImages[] = $imagePath;

        return $imagePath;
    }

    /**
     * @param string $imagePath
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function isRed(string $imagePath, int $x, int $y): bool
    {
        $image = imagecreatefromjpeg($imagePath);
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        return $color['red'] > $color['blue'];
    }
}
