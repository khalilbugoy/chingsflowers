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
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate;

class CacheWarmerAggregateTest extends TestCase
{
    use ForwardCompatTestTrait;

    protected static $cacheDir;

    private static function doSetUpBeforeClass()
    {
        self::$cacheDir = tempnam(sys_get_temp_dir(), 'sf2_cache_warmer_dir');
    }

    private static function doTearDownAfterClass()
    {
        @unlink(self::$cacheDir);
    }

    public function testInjectWarmersUsingConstructor()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new CacheWarmerAggregate([$warmer]);
        $aggregate->warmUp(self::$cacheDir);
    }

    /**
     * @group legacy
     */
    public function testInjectWarmersUsingAdd()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new CacheWarmerAggregate();
        $aggregate->add($warmer);
        $aggregate->warmUp(self::$cacheDir);
    }

    /**
     * @group legacy
     */
    public function testInjectWarmersUsingSetWarmers()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new CacheWarmerAggregate();
        $aggregate->setWarmers([$warmer]);
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testWarmupDoesCallWarmupOnOptionalWarmersWhenEnableOptionalWarmersIsEnabled()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->never())
            ->method('isOptional');
        $warmer
            ->expects($this->once())
            ->method('warmUp');

        $aggregate = new CacheWarmerAggregate([$warmer]);
        $aggregate->enableOptionalWarmers();
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testWarmupDoesNotCallWarmupOnOptionalWarmersWhenEnableOptionalWarmersIsNotEnabled()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('isOptional')
            ->willReturn(true);
        $warmer
            ->expects($this->never())
            ->method('warmUp');

        $aggregate = new CacheWarmerAggregate([$warmer]);
        $aggregate->warmUp(self::$cacheDir);
    }

    protected function getCacheWarmerMock()
    {
        $warmer = $this->getMockBuilder('Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $warmer;
    }
}
