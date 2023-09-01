<?php

namespace Pendable\Mail\Tests;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email as SymfonyEmail;

class PendableServiceProviderTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function appIsUsingPendableDriver()
    {
        $this->assertEquals('pendable', config('mail.default'));
    }


    /**
     * @test
     * @return void
     */
    public function canSendMailable()
    {
        $mailable = new Mailable;
        $mailable->from('noreply@test.com');
        $mailable->to('to@test.com');
        $mailable->subject('Test Subject');

        $mailable->tag('my-tag-1');
        $mailable->tag('my-tag-2');

        $mailable->metadata('custom_1', 'one');
        $mailable->metadata('custom_2', 'two');

        $mailable->withSymfonyMessage(function (SymfonyEmail $message) {
            $message->getHeaders()->addTextHeader('priority', 60);
            $message->getHeaders()->addTextHeader('config_identifier', 'my-config');
            $message->getHeaders()->addTextHeader('client_email_id', '1');
            $message->getHeaders()->addTextHeader('schedule_send_at', '2023-06-25T22:37:26+05:30');
        });

        Mail::fake();
        Mail::send($mailable);
        Mail::assertSentCount(1);
        Mail::assertSent(Mailable::class);
        Mail::sent(Mailable::class, function (Mailable $mail) {
            $mail->assertFrom('noreply@test.com');
            $mail->assertTo('to@test.com');
            $mail->assertHasTag('my-tag-1');
            $mail->assertHasTag('my-tag-2');
            $mail->assertHasMetadata('custom_1', 'one');
            $mail->assertHasMetadata('custom_2', 'two');
        });
    }


}