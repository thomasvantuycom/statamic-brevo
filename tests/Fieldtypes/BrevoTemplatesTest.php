<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Fieldtypes;

use Brevo\Brevo;
use Brevo\Core\Client\MockHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Select;
use ThomasVantuycom\StatamicBrevo\Fieldtypes\BrevoTemplates;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class BrevoTemplatesTest extends TestCase
{
    #[Test]
    public function it_extends_the_select_fieldtype(): void
    {
        $client = new MockHttpClient;
        $field = new BrevoTemplates(new Brevo('apikey', ['client' => $client]));

        $this->assertInstanceOf(Select::class, $field);
        $this->assertSame('select', $field->component());
    }

    #[Test]
    public function it_is_not_selectable(): void
    {
        $client = new MockHttpClient;
        $field = new BrevoTemplates(new Brevo('apikey', ['client' => $client]));

        $this->assertFalse($field->selectable());
        $this->assertFalse($field->selectableInForms());
    }

    #[Test]
    public function it_preloads_brevo_template_options(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'count' => 2,
            'templates' => [
                $this->template(1, 'Template 1'),
                $this->template(2, 'Template 2'),
            ],
        ]));

        $field = new BrevoTemplates(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/smtp/templates?templateStatus=true&limit=50', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [
                ['value' => 1, 'label' => 'Template 1'],
                ['value' => 2, 'label' => 'Template 2'],
            ],
        ], $result);
    }

    #[Test]
    public function it_preloads_brevo_template_options_across_multiple_pages(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'count' => 51,
            'templates' => [
                ...array_map(fn (int $id) => $this->template($id, "Template {$id}"), range(1, 50)),
            ],
        ]), $this->response([
            'count' => 51,
            'templates' => [
                $this->template(51, 'Template 51'),
            ],
        ]));

        $field = new BrevoTemplates(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/smtp/templates?templateStatus=true&limit=50&offset=50', $lastRequest->getUri());
        $this->assertSame(2, $client->getRequestCount());

        $this->assertSame([
            'options' => array_map(fn (int $id) => [
                'value' => $id,
                'label' => "Template {$id}",
            ], range(1, 51)),
        ], $result);
    }

    #[Test]
    public function it_preloads_an_empty_options_list_when_brevo_has_no_templates(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([]));

        $field = new BrevoTemplates(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/smtp/templates?templateStatus=true&limit=50', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [],
        ], $result);
    }

    protected function template(int $id, string $name): array
    {
        return [
            'createdAt' => '2026-01-01T00:00:00.000Z',
            'htmlContent' => '<p>Hello</p>',
            'id' => $id,
            'isActive' => true,
            'modifiedAt' => '2026-01-01T00:00:00.000Z',
            'name' => $name,
            'replyTo' => 'hello@example.com',
            'sender' => [
                'email' => 'hello@example.com',
                'id' => '1',
                'name' => 'Example',
            ],
            'subject' => $name,
            'tag' => '',
            'testSent' => false,
            'toField' => '',
        ];
    }

    protected function response(array $payload): Response
    {
        return new Response(body: json_encode($payload));
    }
}
