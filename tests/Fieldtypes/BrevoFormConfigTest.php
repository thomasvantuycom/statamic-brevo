<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Group;
use ThomasVantuycom\StatamicBrevo\Fieldtypes\BrevoFormConfig;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class BrevoFormConfigTest extends TestCase
{
    #[Test]
    public function it_extends_the_group_fieldtype()
    {
        $this->assertInstanceOf(Group::class, new BrevoFormConfig);
    }

    #[Test]
    public function it_is_not_selectable(): void
    {
        $fieldtype = new BrevoFormConfig;

        $this->assertFalse($fieldtype->selectable());
        $this->assertFalse($fieldtype->selectableInForms());
    }

    #[Test]
    #[DataProvider('preProcessProvider')]
    public function it_sets_enabled_during_preprocess(mixed $value, bool $expected): void
    {
        $field = (new BrevoFormConfig)->setField(new Field('test', [
            'type' => 'brevo_form_config',
            'fields' => [
                [
                    'handle' => 'email_field',
                    'field' => ['type' => 'form_field'],
                ],
            ],
        ]));

        $result = $field->preProcess($value);

        $this->assertArrayHasKey('enabled', $result);
        $this->assertSame($expected, $result['enabled']);
    }

    public static function preProcessProvider(): array
    {
        return [
            'no value' => [
                null,
                false,
            ],
            'set value' => [
                ['email_field' => 'email'],
                true,
            ],
        ];
    }

    #[Test]
    public function it_removes_enabled_during_process(): void
    {
        $field = (new BrevoFormConfig)->setField(new Field('test', [
            'type' => 'payment_config',
            'fields' => [
                [
                    'handle' => 'email_field',
                    'field' => ['type' => 'form_field'],
                ],
            ],
        ]));

        $result = $field->process([
            'enabled' => true,
            'email_field' => 'email',
        ]);

        $this->assertSame([
            'email_field' => 'email',
        ], $result);
    }
}
