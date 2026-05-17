<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Contacts;

use Brevo\Brevo;
use Brevo\Core\Client\MockHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\RequestInterface;
use Statamic\Forms\Form;
use Statamic\Forms\Submission;
use ThomasVantuycom\StatamicBrevo\Contacts\Contacts;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class ContactsTest extends TestCase
{
    #[Test]
    public function it_creates_a_contact_from_a_submission(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
            'attribute_fields' => [
                ['field' => 'first_name', 'attribute' => 'FIRSTNAME'],
                ['field' => 'last_name', 'attribute' => 'LASTNAME'],
            ],
            'use_double_opt_in' => false,
        ]));

        $lastRequest = $client->getLastRequest();

        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertEquals([
            'listIds' => [1, 2],
            'email' => 'johndoe@email.com',
            'attributes' => [
                'FIRSTNAME' => 'John',
                'LASTNAME' => 'Doe',
            ],
            'updateEnabled' => true,
        ], $this->requestBody($lastRequest));
    }

    #[Test]
    public function it_creates_a_double_opt_in_contact_from_a_submission(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
            'attribute_fields' => [
                ['field' => 'first_name', 'attribute' => 'FIRSTNAME'],
                ['field' => 'last_name', 'attribute' => 'LASTNAME'],
            ],
            'use_double_opt_in' => true,
            'template' => 3,
            'redirection_url' => 'https://www.example.com/thanks',
        ]));

        $lastRequest = $client->getLastRequest();

        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/doubleOptinConfirmation', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertEquals([
            'includeListIds' => [1, 2],
            'email' => 'johndoe@email.com',
            'attributes' => [
                'FIRSTNAME' => 'John',
                'LASTNAME' => 'Doe',
            ],
            'templateId' => 3,
            'redirectionUrl' => 'https://www.example.com/thanks',
        ], $this->requestBody($lastRequest));
    }

    #[Test]
    public function it_does_not_create_a_contact_when_the_opt_in_field_is_unchecked(): void
    {
        $client = new MockHttpClient;

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'consent' => false,
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
            'opt_in_field' => 'consent',
        ]));

        $this->assertSame(0, $client->getRequestCount());
    }

    #[Test]
    public function it_creates_a_contact_when_no_opt_in_field_is_configured(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
        ]));

        $this->assertSame(1, $client->getRequestCount());
    }

    #[Test]
    public function it_creates_a_contact_when_the_opt_in_field_is_checked(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'consent' => '1',
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
            'opt_in_field' => 'consent',
        ]));

        $this->assertSame(1, $client->getRequestCount());
    }

    protected function submission(array $data, array $brevoConfig): Submission
    {
        $form = (new Form)
            ->handle('subscribe')
            ->set('brevo', $brevoConfig);

        return (new Submission)
            ->form($form)
            ->data($data);
    }

    protected function requestBody(RequestInterface $request): array
    {
        return json_decode((string) $request->getBody(), true);
    }

    protected function response(array $payload): Response
    {
        return new Response(body: json_encode($payload));
    }
}
