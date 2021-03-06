<?php

namespace Swarrot\Broker\MessageProvider;

use Swarrot\Broker\Message;

class PeclPackageMessageProvider implements MessageProviderInterface
{
    private $queue;

    public function __construct(\AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): ?Message
    {
        $envelope = $this->queue->get();

        if (!$envelope instanceof \AMQPEnvelope) {
            return null;
        }

        return new Message(
            $envelope->getBody(),
            [
                'content_type' => $envelope->getContentType(),
                'routing_key' => $envelope->getRoutingKey(),
                'delivery_tag' => $envelope->getDeliveryTag(),
                'delivery_mode' => $envelope->getDeliveryMode(),
                'exchange_name' => $envelope->getExchangeName(),
                'is_redelivery' => $envelope->isRedelivery(),
                'content_encoding' => $envelope->getContentEncoding(),
                'type' => $envelope->getType(),
                'timestamp' => $envelope->getTimeStamp(),
                'priority' => $envelope->getPriority(),
                'expiration' => $envelope->getExpiration(),
                'app_id' => $envelope->getAppId(),
                'message_id' => $envelope->getMessageId(),
                'reply_to' => $envelope->getReplyTo(),
                'correlation_id' => $envelope->getCorrelationId(),
                'headers' => $envelope->getHeaders(),
                'user_id' => $envelope->getUserId(),
                'cluster_id' => 0,
                'channel' => '',
                'consumer_tag' => '',
            ],
            (string) $envelope->getDeliveryTag()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Message $message): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot ack a message without id.');
        }

        $this->queue->ack($id);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Message $message, bool $requeue = false): void
    {
        if (null === $id = $message->getId()) {
            throw new \RuntimeException('Cannot nack a message without id.');
        }

        $this->queue->nack($id, $requeue ? AMQP_REQUEUE : 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName(): string
    {
        return $this->queue->getName();
    }
}
