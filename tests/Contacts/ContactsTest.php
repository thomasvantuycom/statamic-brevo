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

    #[Test]
    public function it_adds_conditional_lists_based_on_submitted_values(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'topics' => ['banking', 'payments'],
            'role' => 'partner',
        ], [
            'lists' => [1],
            'email_field' => 'email',
            'dynamic_lists' => true,
            'conditional_lists' => [
                ['field' => 'topics', 'value' => 'banking', 'lists' => [2]],
                ['field' => 'topics', 'value' => 'payments', 'lists' => [3, 4]],
                ['field' => 'topics', 'value' => 'commerce', 'lists' => [5]],
                ['field' => 'role', 'value' => 'partner', 'lists' => [6]],
                ['field' => 'role', 'value' => 'member', 'lists' => [7]],
            ],
        ]));

        $this->assertSame([1, 2, 3, 4, 6], $this->requestBody($client->getLastRequest())['listIds']);
    }

    #[Test]
    public function it_ignores_conditional_lists_when_dynamic_lists_are_disabled(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'topics' => ['banking'],
        ], [
            'lists' => [1],
            'email_field' => 'email',
            'conditional_lists' => [
                ['field' => 'topics', 'value' => 'banking', 'lists' => [2]],
            ],
        ]));

        $this->assertSame([1], $this->requestBody($client->getLastRequest())['listIds']);
    }

    #[Test]
    public function it_adds_conditional_lists_when_no_value_is_configured(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'newsletter' => true,
            'events' => false,
            'topics' => [],
        ], [
            'lists' => [1],
            'email_field' => 'email',
            'dynamic_lists' => true,
            'conditional_lists' => [
                ['field' => 'newsletter', 'lists' => [2]],
                ['field' => 'events', 'lists' => [3]],
                ['field' => 'topics', 'lists' => [4]],
            ],
        ]));

        $this->assertSame([1, 2], $this->requestBody($client->getLastRequest())['listIds']);
    }

    #[Test]
    public function it_does_not_duplicate_lists_matched_by_multiple_rules(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response(['id' => 1]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'topics' => ['banking', 'payments'],
        ], [
            'lists' => [1, 2],
            'email_field' => 'email',
            'dynamic_lists' => true,
            'conditional_lists' => [
                ['field' => 'topics', 'value' => 'banking', 'lists' => [2, 3]],
                ['field' => 'topics', 'value' => 'payments', 'lists' => [3]],
            ],
        ]));

        $this->assertSame([1, 2, 3], $this->requestBody($client->getLastRequest())['listIds']);
    }

    #[Test]
    public function it_adds_conditional_lists_to_double_opt_in_contacts(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([]));

        $contacts = new Contacts(new Brevo('apikey', ['client' => $client]));

        $contacts->createFromSubmission($this->submission([
            'email' => 'johndoe@email.com',
            'topics' => ['banking'],
        ], [
            'lists' => [1],
            'email_field' => 'email',
            'dynamic_lists' => true,
            'conditional_lists' => [
                ['field' => 'topics', 'value' => 'banking', 'lists' => [2]],
            ],
            'use_double_opt_in' => true,
            'template' => 3,
            'redirection_url' => 'https://www.example.com/thanks',
        ]));

        $this->assertSame([1, 2], $this->requestBody($client->getLastRequest())['includeListIds']);
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
