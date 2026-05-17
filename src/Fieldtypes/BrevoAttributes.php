<?php

namespace ThomasVantuycom\StatamicBrevo\Fieldtypes;

use Brevo\Brevo;
use Statamic\Fieldtypes\Select;

class BrevoAttributes extends Select
{
    protected $component = 'select';
    protected $selectable = false;
    protected $selectableInForms = false;

    public function __construct(protected Brevo $client) {}

    protected function getOptions(): array
    {
        $options = [];

        $response = $this->client->contacts->getAttributes();

        foreach ($response->attributes as $attribute) {
            if ($attribute->calculatedValue === null) {
                $options[] = [
                    'value' => $attribute->name,
                    'label' => $attribute->name,
                ];
            }
        }

        usort($options, function ($a, $b) {
            return strnatcmp($a['label'], $b['label']);
        });

        return $options;
    }
}
