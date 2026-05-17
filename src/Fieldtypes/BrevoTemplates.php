<?php

namespace ThomasVantuycom\StatamicBrevo\Fieldtypes;

use Brevo\Brevo;
use Brevo\TransactionalEmails\Requests\GetSmtpTemplatesRequest;
use Statamic\Fieldtypes\Select;

class BrevoTemplates extends Select
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
            $response = $this->client->transactionalEmails->getSmtpTemplates(new GetSmtpTemplatesRequest([
                'templateStatus' => true,
                'limit' => $limit,
                'offset' => $offset,
            ]));

            foreach (($response->templates ?? []) as $template) {
                $options[] = [
                    'value' => $template->id,
                    'label' => $template->name,
                ];
            }

            $offset += $limit;
        } while ($offset < ($response->count ?? 0));

        return $options;
    }
}
