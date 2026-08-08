<?php

namespace ThomasVantuycom\StatamicBrevo\Tests\Fieldtypes;

use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\Form as FormFacade;
use Statamic\Fields\Blueprint;
use Statamic\Fieldtypes\Select;
use Statamic\Forms\Form;
use ThomasVantuycom\StatamicBrevo\Fieldtypes\FormField;
use ThomasVantuycom\StatamicBrevo\Tests\TestCase;

class FormFieldTest extends TestCase
{
    #[Test]
    public function it_extends_the_select_fieldtype()
    {
        $field = new FormField;

        $this->assertInstanceOf(Select::class, $field);
        $this->assertSame('select', $field->component());
    }

    #[Test]
    public function it_is_not_selectable()
    {
        $field = new FormField;

        $this->assertFalse($field->selectable());
        $this->assertFalse($field->selectableInForms());
    }

    #[Test]
    public function it_preloads_form_field_options_when_on_the_form_edit_screen()
    {
        BlueprintFacade::shouldReceive('find')
            ->once()
            ->with('forms.subscribe')
            ->andReturn(
                (new Blueprint)
                    ->setContents([
                        'fields' => [
                            [
                                'handle' => 'first_name',
                                'field' => [
                                    'type' => 'text',
                                    'display' => 'First Name',
                                ],
                            ],
                            [
                                'handle' => 'last_name',
                                'field' => [
                                    'type' => 'text',
                                    'display' => 'Last Name',
                                ],
                            ],
                        ],
                    ])
            );

        FormFacade::shouldReceive('find')
            ->once()
            ->with('subscribe')
            ->andReturn(
                (new Form)->handle('subscribe')
            );

        Request::swap(Request::create('/cp/forms/subscribe'));

        $field = new FormField;
        $result = $field->preload();

        $this->assertSame([
            'options' => [
                ['value' => 'first_name', 'label' => 'First Name'],
                ['value' => 'last_name', 'label' => 'Last Name'],
            ],
        ], $result);
    }

    #[Test]
    public function it_preloads_an_empty_options_list_when_the_form_has_no_fields()
    {
        BlueprintFacade::shouldReceive('find')
            ->once()
            ->with('forms.empty')
            ->andReturn(new Blueprint);

        FormFacade::shouldReceive('find')
            ->once()
            ->with('empty')
            ->andReturn(
                (new Form)->handle('empty')
            );

        Request::swap(Request::create('/cp/forms/empty'));

        $field = new FormField;
        $result = $field->preload();

        $this->assertSame([
            'options' => [],
        ], $result);
    }
}
