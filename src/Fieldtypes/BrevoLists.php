<?php

namespace ThomasVantuycom\StatamicBrevo\Fieldtypes;

use Brevo\Brevo;
use Brevo\Contacts\Requests\GetListsRequest;
use Statamic\Fieldtypes\Select;

class BrevoLists extends Select
{
    protected $component = 'select';
    protected $selectable = false;
    protected $selectableInForms = false;

    public function __construct(protected Brevo $client) {}

    protected function getOptions(): array
    {
        $options = [];
        $limit = 50;
        $offset = 0;

        do {
            $response = $this->client->contacts->getLists(new GetListsRequest([
                'limit' => $limit,
                'offset' => $offset,
            ]));

            foreach (($response->lists ?? []) as $list) {
                $options[] = [
                    'value' => $list->id,
                    'label' => $list->name,
                ];
            }

            $offset += $limit;
        } while ($offset < ($response->count ?? 0));

        return $options;
    }
}
