<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ForwardCompatTestTrait;
use Symfony\Component\Translation\MessageCatalogue;

class MessageCatalogueTest extends TestCase
{
    use ForwardCompatTestTrait;

    public function testGetLocale()
    {
        $catalogue = new MessageCatalogue('en');

        $this->assertEquals('en', $catalogue->getLocale());
    }

    public function testGetDomains()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => [], 'domain2' => []]);

        $this->assertEquals(['domain1', 'domain2'], $catalogue->getDomains());
    }

    public function testAll()
    {
        $catalogue = new MessageCatalogue('en', $messages = ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);

        $this->assertEquals(['foo' => 'foo'], $catalogue->all('domain1'));
        $this->assertEquals([], $catalogue->all('domain88'));
        $this->assertEquals($messages, $catalogue->all());
    }

    public function testHas()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);

        $this->assertTrue($catalogue->has('foo', 'domain1'));
        $this->assertFalse($catalogue->has('bar', 'domain1'));
        $this->assertFalse($catalogue->has('foo', 'domain88'));
    }

    public function testGetSet()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->set('foo1', 'foo1', 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));
    }

    public function testAdd()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->add(['foo1' => 'foo1'], 'domain1');

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain1');
        $this->assertEquals('bar', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $catalogue->add(['foo' => 'bar'], 'domain88');
        $this->assertEquals('bar', $catalogue->get('foo', 'domain88'));
    }

    public function testReplace()
    {
        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->replace($messages = ['foo1' => 'foo1'], 'domain1');

        $this->assertEquals($messages, $catalogue->all('domain1'));
    }

    public function testAddCatalogue()
    {
        $r = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r->expects($this->any())->method('__toString')->willReturn('r');

        $r1 = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r1->expects($this->any())->method('__toString')->willReturn('r1');

        $catalogue = new MessageCatalogue('en', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->addResource($r);

        $catalogue1 = new MessageCatalogue('en', ['domain1' => ['foo1' => 'foo1']]);
        $catalogue1->addResource($r1);

        $catalogue->addCatalogue($catalogue1);

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $this->assertEquals([$r, $r1], $catalogue->getResources());
    }

    public function testAddFallbackCatalogue()
    {
        $r = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r->expects($this->any())->method('__toString')->willReturn('r');

        $r1 = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r1->expects($this->any())->method('__toString')->willReturn('r1');

        $r2 = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r2->expects($this->any())->method('__toString')->willReturn('r2');

        $catalogue = new MessageCatalogue('fr_FR', ['domain1' => ['foo' => 'foo'], 'domain2' => ['bar' => 'bar']]);
        $catalogue->addResource($r);

        $catalogue1 = new MessageCatalogue('fr', ['domain1' => ['foo' => 'bar', 'foo1' => 'foo1']]);
        $catalogue1->addResource($r1);

        $catalogue2 = new MessageCatalogue('en');
        $catalogue2->addResource($r2);

        $catalogue->addFallbackCatalogue($catalogue1);
        $catalogue1->addFallbackCatalogue($catalogue2);

        $this->assertEquals('foo', $catalogue->get('foo', 'domain1'));
        $this->assertEquals('foo1', $catalogue->get('foo1', 'domain1'));

        $this->assertEquals([$r, $r1, $r2], $catalogue->getResources());
    }

    public function testAddFallbackCatalogueWithParentCircularReference()
    {
        $this->expectException('Symfony\Component\Translation\Exception\LogicException');
        $main = new MessageCatalogue('en_US');
        $fallback = new MessageCatalogue('fr_FR');

        $fallback->addFallbackCatalogue($main);
        $main->addFallbackCatalogue($fallback);
    }

    public function testAddFallbackCatalogueWithFallbackCircularReference()
    {
        $this->expectException('Symfony\Component\Translation\Exception\LogicException');
        $fr = new MessageCatalogue('fr');
        $en = new MessageCatalogue('en');
        $es = new MessageCatalogue('es');

        $fr->addFallbackCatalogue($en);
        $es->addFallbackCatalogue($en);
        $en->addFallbackCatalogue($fr);
    }

    public function testAddCatalogueWhenLocaleIsNotTheSameAsTheCurrentOne()
    {
        $this->expectException('Symfony\Component\Translation\Exception\LogicException');
        $catalogue = new MessageCatalogue('en');
        $catalogue->addCatalogue(new MessageCatalogue('fr', []));
    }

    public function testGetAddResource()
    {
        $catalogue = new MessageCatalogue('en');
        $r = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r->expects($this->any())->method('__toString')->willReturn('r');
        $catalogue->addResource($r);
        $catalogue->addResource($r);
        $r1 = $this->getMockBuilder('Symfony\Component\Config\Resource\ResourceInterface')->getMock();
        $r1->expects($this->any())->method('__toString')->willReturn('r1');
        $catalogue->addResource($r1);

        $this->assertEquals([$r, $r1], $catalogue->getResources());
    }

    public function testMetadataDelete()
    {
        $catalogue = new MessageCatalogue('en');
        $this->assertEquals([], $catalogue->getMetadata('', ''), 'Metadata is empty');
        $catalogue->deleteMetadata('key', 'messages');
        $catalogue->deleteMetadata('', 'messages');
        $catalogue->deleteMetadata();
    }

    public function testMetadataSetGetDelete()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->setMetadata('key', 'value');
        $this->assertEquals('value', $catalogue->getMetadata('key', 'messages'), "Metadata 'key' = 'value'");

        $catalogue->setMetadata('key2', []);
        $this->assertEquals([], $catalogue->getMetadata('key2', 'messages'), 'Metadata key2 is array');

        $catalogue->deleteMetadata('key2', 'messages');
        $this->assertNull($catalogue->getMetadata('key2', 'messages'), 'Metadata key2 should is deleted.');

        $catalogue->deleteMetadata('key2', 'domain');
        $this->assertNull($catalogue->getMetadata('key2', 'domain'), 'Metadata key2 should is deleted.');
    }

    public function testMetadataMerge()
    {
        $cat1 = new MessageCatalogue('en');
        $cat1->setMetadata('a', 'b');
        $this->assertEquals(['messages' => ['a' => 'b']], $cat1->getMetadata('', ''), 'Cat1 contains messages metadata.');

        $cat2 = new MessageCatalogue('en');
        $cat2->setMetadata('b', 'c', 'domain');
        $this->assertEquals(['domain' => ['b' => 'c']], $cat2->getMetadata('', ''), 'Cat2 contains domain metadata.');

        $cat1->addCatalogue($cat2);
        $this->assertEquals(['messages' => ['a' => 'b'], 'domain' => ['b' => 'c']], $cat1->getMetadata('', ''), 'Cat1 contains merged metadata.');
    }
}
