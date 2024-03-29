<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\CacheClearer;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ForwardCompatTestTrait;
use Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer;

class ChainCacheClearerTest extends TestCase
{
    use ForwardCompatTestTrait;

    protected static $cacheDir;

    private static function doSetUpBeforeClass()
    {
        self::$cacheDir = tempnam(sys_get_temp_dir(), 'sf2_cache_clearer_dir');
    }

    private static function doTearDownAfterClass()
    {
        @unlink(self::$cacheDir);
    }

    public function testInjectClearersInConstructor()
    {
        $clearer = $this->getMockClearer();
        $clearer
            ->expects($this->once())
            ->method('clear');

        $chainClearer = new ChainCacheClearer([$clearer]);
        $chainClearer->clear(self::$cacheDir);
    }

    /**
     * @group legacy
     */
    public function testInjectClearerUsingAdd()
    {
        $clearer = $this->getMockClearer();
        $clearer
            ->expects($this->once())
            ->method('clear');

        $chainClearer = new ChainCacheClearer();
        $chainClearer->add($clearer);
        $chainClearer->clear(self::$cacheDir);
    }

    protected function getMockClearer()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface')->getMock();
    }
}
