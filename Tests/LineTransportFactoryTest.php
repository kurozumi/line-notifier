<?php

namespace Kurozumi\Symfony\Component\Notifier\Bridge\Line\Tests;

use Kurozumi\Symfony\Component\Notifier\Bridge\Line\LineTransportFactory;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;

class LineTransportFactoryTest extends TransportFactoryTestCase
{
    public function createFactory(): LineTransportFactory
    {
        return new LineTransportFactory();
    }

    public function createProvider(): iterable
    {
        yield [
            'line://host.test',
            'line://token@host.test',
        ];
    }

    public function supportsProvider(): iterable
    {
        yield [true, 'line://host'];
        yield [false, 'somethingElse://host'];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield 'missing token' => ['line://host.test'];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://token@host'];
    }
}