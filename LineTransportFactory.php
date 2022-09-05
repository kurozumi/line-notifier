<?php

namespace Kurozumi\Symfony\Component\Notifier\Bridge\Line;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class LineTransportFactory extends AbstractTransportFactory
{
    protected function getSupportedSchemes(): array
    {
        return ['line'];
    }

    public function create(Dsn $dsn): TransportInterface
    {
        if ('line' !== $dsn->getScheme()) {
            throw new UnsupportedSchemeException($dsn, 'line', $this->getSupportedSchemes());
        }

        $token = $this->getUser($dsn);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new LineTransport($token, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }
}