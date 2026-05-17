<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Fieldtypes;

use Brevo\Brevo;
use Brevo\Core\Client\MockHttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Select;
use ThomasVantuycom\StatamicBrevo\Fieldtypes\BrevoAttributes;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class BrevoAttributesTest extends TestCase
{
    #[Test]
    public function it_extends_the_select_fieldtype()
    {
        $client = new MockHttpClient;
        $field = new BrevoAttributes(new Brevo('apikey', ['client' => $client]));

        $this->assertInstanceOf(Select::class, $field);
        $this->assertSame('select', $field->component());
    }

    #[Test]
    public function it_is_not_selectable()
    {
        $client = new MockHttpClient;
        $field = new BrevoAttributes(new Brevo('apikey', ['client' => $client]));

        $this->assertFalse($field->selectable());
        $this->assertFalse($field->selectableInForms());
    }

    #[Test]
    public function it_preloads_brevo_attribute_options()
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'attributes' => [
                ['name' => 'FIRSTNAME', 'category' => 'normal', 'type' => 'text'],
                ['name' => 'LASTNAME', 'category' => 'normal', 'type' => 'text'],
            ],
        ]));

        $field = new BrevoAttributes(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/attributes', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [
                ['value' => 'FIRSTNAME', 'label' => 'FIRSTNAME'],
                ['value' => 'LASTNAME', 'label' => 'LASTNAME'],
            ],
        ], $result);
    }

    #[Test]
    public function it_preloads_brevo_attribute_options_but_excludes_calculated_attributes()
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'attributes' => [
                ['name' => 'BLACKLIST', 'category' => 'global', 'type' => 'float', 'calculatedValue' => 'COUNT[BLACKLISTED,BLACKLISTED,<,NOW()]'],
                ['name' => 'FIRSTNAME', 'category' => 'normal', 'type' => 'text'],
                ['name' => 'LASTNAME', 'category' => 'normal', 'type' => 'text'],
            ],
        ]));

        $field = new BrevoAttributes(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $lastRequest = $client->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringEndsWith('/contacts/attributes', $lastRequest->getUri());
        $this->assertSame(1, $client->getRequestCount());

        $this->assertSame([
            'options' => [
                ['value' => 'FIRSTNAME', 'label' => 'FIRSTNAME'],
                ['value' => 'LASTNAME', 'label' => 'LASTNAME'],
            ],
        ], $result);
    }

    #[Test]
    public function it_preloads_brevo_attribute_options_in_natural_sort_order()
    {
        $client = new MockHttpClient;
        $client->append($this->response([
            'attributes' => [
                ['name' => 'FIELD10', 'category' => 'normal', 'type' => 'text'],
                ['name' => 'FIELD2', 'category' => 'normal', 'type' => 'text'],
                ['name' => 'FIELD1', 'category' => 'normal', 'type' => 'text'],
            ],
        ]));

        $field = new BrevoAttributes(new Brevo('apikey', ['client' => $client]));
        $result = $field->preload();

        $this->assertSame([
            'options' => [
                ['value' => 'FIELD1', 'label' => 'FIELD1'],
                ['value' => 'FIELD2', 'label' => 'FIELD2'],
                ['value' => 'FIELD10', 'label' => 'FIELD10'],
            ],
        ], $result);
    }

    protected function response(array $payload)
    {
        return new Response(body: json_encode($payload));
    }
}
