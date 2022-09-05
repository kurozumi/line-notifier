<?php

namespace Kurozumi\Symfony\Component\Notifier\Bridge\Line;

use Symfony\Component\Notifier\Exception\LengthException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class LineTransport extends AbstractTransport
{
    protected const HOST = 'notify-api.line.me';

    private const SUBJECT_LIMIT = 1000;

    private $token;

    public function __construct(string $token, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->token = $token;
        $this->client = $client;

        parent::__construct($client, $dispatcher);
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $content = $message->getSubject();

        if (mb_strlen($content, 'UTF-8') > self::SUBJECT_LIMIT) {
            throw new LengthException(sprintf('The subject length of a Line message must not exceed %d characters.', self::SUBJECT_LIMIT));
        }

        $endpoint = sprintf('https://%s/api/notify', $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'auth_bearer' => $this->token,
            'query' => [
                'message' => $content
            ]
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Line server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            $result = $response->toArray(false);

            if (401 === $statusCode) {
                $originalContent = $message->getSubject();
                $errorMessage = $result['message'];
                $errorCode = $result['code'];
                throw new TransportException(sprintf('Unable to post the Line message: "%s" (%d: "%s").', $originalContent, $errorCode, $errorMessage), $response);
            }

            if (400 === $statusCode) {
                $originalContent = $message->getSubject();

                $errorMessage = '';
                foreach ($result as $fieldName => $message) {
                    $message = \is_array($message) ? implode(' ', $message) : $message;
                    $errorMessage .= $fieldName . ': ' . $message . ' ';
                }

                $errorMessage = trim($errorMessage);
                throw new TransportException(sprintf('Unable to post the Line message: "%s" (%s).', $originalContent, $errorMessage), $response);
            }

            throw new TransportException(sprintf('Unable to post the Line message: "%s" (Status Code: %d).', $message->getSubject(), $statusCode), $response);
        }

        return new SentMessage($message, (string)$this);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage;
    }

    public function __toString(): string
    {
        return sprintf('line://%s', $this->getEndpoint());
    }
}