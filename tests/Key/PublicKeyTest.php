<?php

namespace BitWasp\Bitcoin\Tests\Key;

use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\EcAdapter\Adapter\EcAdapterInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Key\PublicKey;
use BitWasp\Bitcoin\Key\PublicKeyFactory;
use BitWasp\Bitcoin\Tests\AbstractTestCase;

class PublicKeyTest extends AbstractTestCase
{
    public function getPublicVectors()
    {
        $json = json_decode($this->dataFile('publickey.compressed.json'));
        $results = [];
        foreach ($json->test as $test) {
            foreach ($this->getEcAdapters() as $adapter) {
                $results[] = [
                    $adapter[0],
                    $test->compressed,
                    $test->uncompressed
                ];
            }
        }

        return $results;
    }

    /**
     * @dataProvider getPublicVectors
     * @param EcAdapterInterface $ecAdapter
     * @param string $eCompressed
     * @param string $eUncompressed
     */
    public function testFromHex(EcAdapterInterface $ecAdapter, $eCompressed, $eUncompressed)
    {
        $publicKey = PublicKeyFactory::fromHex($eCompressed, $ecAdapter);

        $this->assertSame($eCompressed, $publicKey->getBuffer()->getHex());
        $this->assertSame($publicKey->getBuffer()->getHex(), $eCompressed);
        $this->assertTrue($publicKey->isCompressed());
    }

    /**
     * @dataProvider getPublicVectors
     * @param EcAdapterInterface $ecAdapter
     * @param $eCompressed
     * @param $eUncompressed
     */
    public function testFromHexUncompressed(EcAdapterInterface $ecAdapter, $eCompressed, $eUncompressed)
    {
        $publicKey = PublicKeyFactory::fromHex($eUncompressed, $ecAdapter);
        $this->assertSame($eUncompressed, $publicKey->getBuffer()->getHex());
        $this->assertSame($publicKey->getBuffer()->getHex(), $eUncompressed);
        $this->assertFalse($publicKey->isCompressed());
        $this->assertFalse($publicKey->isPrivate());
        
    }

    /**
     * @dataProvider getEcAdapters
     * @param EcAdapterInterface $ecAdapter
     * @expectedException \Exception
     */
    public function testFromHexInvalidLength(EcAdapterInterface $ecAdapter)
    {
        $hex = '02cffc9fcdc2a4e6f5dd91aee9d8d79828c1c93e7a76949a451aab8be6a0c44febaa';
        PublicKeyFactory::fromHex($hex, $ecAdapter);
    }

    /**
     * @expectedException \Exception
     */
    public function testFromHexInvalidByte()
    {
        $hex = '01cffc9fcdc2a4e6f5dd91aee9d8d79828c1c93e7a76949a451aab8be6a0c44feb';
        PublicKeyFactory::fromHex($hex);
    }

    public function testIsCompressedOrUncompressed()
    {
        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('00')));
        $this->assertTrue(PublicKey::isCompressedOrUncompressed(Buffer::hex('0400010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900010203')));
        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('0400010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304050607080900')));
        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('040001020304050607080900010203040506070809000102030405060708090001020304050607080900010203040506070809000102030405060708090001020304')));

        $this->assertTrue(PublicKey::isCompressedOrUncompressed(Buffer::hex('020001020304050607080900010203040506070809000102030405060708090001')));
        $this->assertTrue(PublicKey::isCompressedOrUncompressed(Buffer::hex('030001020304050607080900010203040506070809000102030405060708090001')));
        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('03000102030405060708090001020304050607080900010203040506070809000102')));
        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('0300010203040506070809000102030405060708090001020304050607080900')));

        $this->assertFalse(PublicKey::isCompressedOrUncompressed(Buffer::hex('050001020304050607080900010203040506070809000102030405060708090001')));

    }

    /**
     * @expectedException \Exception
     */
    public function testFromHexInvalidByte2()
    {
        $hex = '04cffc9fcdc2a4e6f5dd91aee9d8d79828c1c93e7a76949a451aab8be6a0c44feb';
        PublicKeyFactory::fromHex($hex);
    }

    public function getPkHashVectors()
    {
        $json = json_decode($this->dataFile('publickey.pubkeyhash.json'));
        $results = [];

        foreach ($json->test as $test) {
            $results[] = [
                $test->key,
                $test->hash
            ];
        }
        
        return $results;
    }

    /**
     * @dataProvider getPkHashVectors
     * @param string $eKey - hex public key
     * @param string $eHash - hex sha256ripemd160 of public key
     */
    public function testPubKeyHash($eKey, $eHash)
    {
        $this->assertSame(
            $eHash,
            PublicKeyFactory::fromHex($eKey)
                ->getPubKeyHash()
                ->getHex()
        );
    }

    /**
     * @param EcAdapterInterface $ecAdapter
     * @param $eCompressed
     * @param $eUncompressed
     * @dataProvider getPublicVectors
     */
    public function testIsNotCompressed(EcAdapterInterface $ecAdapter, $eCompressed, $eUncompressed)
    {
        $pub = PublicKeyFactory::fromHex($eCompressed, $ecAdapter);
        $this->assertTrue($pub->isCompressed());

        $pub = PublicKeyFactory::fromHex($eUncompressed, $ecAdapter);
        $this->assertFalse($pub->isCompressed());
    }
}
