<?php

namespace ThomasVantuycom\StatamicBrevo\Fieldtypes;

use Statamic\Fieldtypes\Group;
use Statamic\Support\Arr;

class BrevoFormConfig extends Group
{
    protected $selectable = false;
    protected $selectableInForms = false;

    public function preProcess($data)
    {
        $enabled = ! is_null($data);

        return array_merge(parent::preProcess($data), [
            'enabled' => $enabled,
        ]);
    }

    public function process($data)
    {
        return Arr::except(parent::process($data), 'enabled');
    }
}
