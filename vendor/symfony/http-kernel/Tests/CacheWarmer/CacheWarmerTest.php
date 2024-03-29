<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\CacheWarmer;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ForwardCompatTestTrait;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

class CacheWarmerTest extends TestCase
{
    use ForwardCompatTestTrait;

    protected static $cacheFile;

    private static function doSetUpBeforeClass()
    {
        self::$cacheFile = tempnam(sys_get_temp_dir(), 'sf2_cache_warmer_dir');
    }

    private static function doTearDownAfterClass()
    {
        @unlink(self::$cacheFile);
    }

    public function testWriteCacheFileCreatesTheFile()
    {
        $warmer = new TestCacheWarmer(self::$cacheFile);
        $warmer->warmUp(\dirname(self::$cacheFile));

        $this->assertFileExists(self::$cacheFile);
    }

    public function testWriteNonWritableCacheFileThrowsARuntimeException()
    {
        $this->expectException('RuntimeException');
        $nonWritableFile = '/this/file/is/very/probably/not/writable';
        $warmer = new TestCacheWarmer($nonWritableFile);
        $warmer->warmUp(\dirname($nonWritableFile));
    }
}

class TestCacheWarmer extends CacheWarmer
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function warmUp($cacheDir)
    {
        $this->writeCacheFile($this->file, 'content');
    }

    public function isOptional()
    {
        return false;
    }
}
