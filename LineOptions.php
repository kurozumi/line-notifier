<?php

namespace Kurozumi\Notifier\Bridge\Line;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

final class LineOptions implements MessageOptionsInterface
{
    private $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function getRecipientId(): ?string
    {
        return '';
    }
}