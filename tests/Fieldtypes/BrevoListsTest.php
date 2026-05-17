<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Fieldtypes;

use Brevo\Brevo;
use Brevo\Core\Client\MockHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Select;
use ThomasVantuycom\StatamicBrevo\Fieldtypes\BrevoLists;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class BrevoListsTest extends TestCase
{
    #[Test]
    public function it_extends_the_select_fieldtype(): void
    {
        $client = new MockHttpClient;
        $field = new BrevoLists(new Brevo('apikey', ['client' => $client]));

        $this->assertInstanceOf(Select::class, $field);
        $this->assertSame('select', $field->component());
    }

    #[Test]
    public function it_is_not_selectable(): void
    {
        $client = new MockHttpClient;
        $field = new BrevoLists(new Brevo('apikey', ['client' => $client]));

        $this->assertFalse($field->selectable());
        $this->assertFalse($field->selectableInForms());
    }

    #[Test]
    public function it_preloads_brevo_list_options(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'count' => 2,
            'lists' => [
                $this->list(1, 'List 1'),
                $this->list(2, 'List 2'),
            ],
        ]));

        $field = new BrevoLists(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/lists?limit=50', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [
                ['value' => 1, 'label' => 'List 1'],
                ['value' => 2, 'label' => 'List 2'],
            ],
        ], $result);
    }

    #[Test]
    public function it_preloads_brevo_list_options_across_multiple_pages(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'count' => 51,
            'lists' => [
                ...array_map(fn (int $id) => $this->list($id, "List {$id}"), range(1, 50)),
            ],
        ]), $this->response([
            'count' => 51,
            'lists' => [
                $this->list(51, 'List 51'),
            ],
        ]));

        $field = new BrevoLists(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/lists?limit=50&offset=50', $lastRequest->getUri());
        $this->assertSame(2, $client->getRequestCount());

        $this->assertSame([
            'options' => array_map(fn (int $id) => [
                'value' => $id,
                'label' => "List {$id}",
            ], range(1, 51)),
        ], $result);
    }

    #[Test]
    public function it_preloads_an_empty_options_list_when_brevo_has_no_lists(): void
    {
        $client = new MockHttpClient;
        $client->append($this->response([]));

        $field = new BrevoLists(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/lists?limit=50', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [],
        ], $result);
    }

    protected function list(int $id, string $name): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'totalBlacklisted' => 0,
            'totalSubscribers' => 0,
            'uniqueSubscribers' => 0,
            'folderId' => 1,
        ];
    }

    protected function response(array $payload): Response
    {
        return new Response(body: json_encode($payload));
    }
}
