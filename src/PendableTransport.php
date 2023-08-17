<?php

namespace Pendable\Mail;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\SentMessage;
use Illuminate\Http\Client\Factory as Http;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Transport\TransportInterface;
use function implode;
use function in_array;
use function array_map;
use function array_merge;
use function json_decode;
use function array_filter;
use const JSON_OBJECT_AS_ARRAY;

class PendableTransport implements TransportInterface
{
    protected const BYPASS_HEADERS = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content-type',
        'sender',
        'reply-to',
    ];

    public function __construct(
        protected Http   $http,
        protected string $token,
    )
    {
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $envelope = $envelope ?? Envelope::create($message);

        $sentMessage = new SentMessage($message, $envelope);

        $email = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $response = $this->http
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->post($this->getApiEndpoint(), $this->getPayload($email, $envelope));

        if ($response->ok()) {
            $sentMessage->setMessageId($response->json('pendable_id'));
            return $sentMessage;
        }

        throw new PendableTransportException(
            $response->json('Message'),
            $response->json('ErrorCode'),
            $response->toException(),
        );
    }

    protected function getApiEndpoint(): string
    {
        return config('services.pendable.endpoint', 'https:/api.pendable.io') . '/email';
    }

    protected function getAttachments(Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();

            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');
            $disposition = $headers->getHeaderBody('Content-Disposition');

            $attributes = [
                'Name' => $filename,
                'Content' => $attachment->bodyToString(),
                'ContentType' => $headers->get('Content-Type')->getBody(),
            ];

            if ($disposition === 'inline') {
                $attributes['ContentID'] = 'cid:' . $filename;
            }

            $attachments[] = $attributes;
        }

        return $attachments;
    }

    protected function getPayload(Email $email, Envelope $envelope): array
    {

        $payload = [
            'from' => $envelope->getSender()->toString(),
            'to' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
            'cc' => $this->stringifyAddresses($email->getCc()),
            'Bcc' => $this->stringifyAddresses($email->getBcc()),
            'subject' => $email->getSubject(),
            'html_body' => $email->getHtmlBody(),
            'text_body' => $email->getTextBody(),
            'reply_to' => $this->stringifyAddresses($email->getReplyTo()),
            // 'Attachments' => $this->getAttachments($email),
            // 'MessageStream' => $this->messageStreamId ?? '',
        ];

        foreach ($email->getHeaders()->all() as $name => $header) {
            if (in_array($name, self::BYPASS_HEADERS, true)) {
                continue;
            }

            if ($header instanceof TagHeader) {
                $payload['Tag'] = $header->getValue();

                continue;
            }

            if ($header instanceof MetadataHeader) {
                $payload['Metadata'][$header->getKey()] = $header->getValue();

                continue;
            }


            $payload['Headers'][] = [
                'Name' => $name,
                'Value' => $header->getBodyAsString(),
            ];
        }

        if ($content = $this->getTemplatedContent($email)) {
            $payload['TemplateId'] = $content['id'] ?? null;
            $payload['TemplateAlias'] = $content['alias'] ?? null;
            $payload['TemplateModel'] = $content['model'] ?? null;

            unset($payload['Subject'], $payload['HtmlBody'], $payload['TextBody']);
        }

        return array_filter($payload);
    }

    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $copies = array_merge($email->getCc(), $email->getBcc());

        return array_filter($envelope->getRecipients(), function (Address $address) use ($copies) {
            return in_array($address, $copies, true) === false;
        });
    }


    protected function getTemplatedContent(Email $email): ?array
    {
        return json_decode($email->getHtmlBody(), flags: JSON_OBJECT_AS_ARRAY);
    }

    protected function stringifyAddresses(array $addresses): string
    {
        return implode(',', array_map(fn(Address $address) => $address->toString(), $addresses));
    }

    public function __toString(): string
    {
        return 'pendable';
    }
}