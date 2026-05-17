<?php

namespace ThomasVantuycom\StatamicBrevo\Fieldtypes;

use Illuminate\Support\Facades\Request;
use Statamic\Facades\Form;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Select;

class FormField extends Select
{
    protected $component = 'select';
    protected $selectable = false;
    protected $selectableInForms = false;

    protected function getOptions(): array
    {
        $handle = Request::segment(3);
        $form = Form::find($handle);

        $options = $form->fields()
            ->map(fn (Field $field) => [
                'value' => $field->handle(),
                'label' => $field->display(),
            ])
            ->sortBy('label')
            ->values()
            ->all();

        return $options;
    }
}
