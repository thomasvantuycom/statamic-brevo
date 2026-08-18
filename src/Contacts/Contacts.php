<?php

namespace ThomasVantuycom\StatamicBrevo\Contacts;

use Brevo\Brevo;
use Brevo\Contacts\Requests\CreateContactRequest;
use Brevo\Contacts\Requests\CreateDoiContactRequest;
use Illuminate\Support\Collection;
use Statamic\Forms\Submission;

class Contacts
{
    public function __construct(protected Brevo $client) {}

    public function create(
        array $lists,
        string $email,
        array $attributes = [],
    ): void {
        $this->client->contacts->createContact(new CreateContactRequest([
            'listIds' => $lists,
            'email' => $email,
            'attributes' => $attributes,
            'updateEnabled' => true,
        ]));
    }

    public function createUsingDoubleOptIn(
        array $lists,
        string $email,
        int $template,
        string $redirectionUrl,
        array $attributes = [],
    ): void {
        $this->client->contacts->createDoiContact(new CreateDoiContactRequest([
            'includeListIds' => $lists,
            'email' => $email,
            'attributes' => $attributes,
            'templateId' => $template,
            'redirectionUrl' => $redirectionUrl,
        ]));
    }

    public function createFromSubmission(Submission $submission): void
    {
        $form = $submission->form();
        $config = collect($form->get('brevo'));

        $optInField = $config->get('opt_in_field');

        if ($optInField) {
            $optIn = filter_var($submission->get($optInField), FILTER_VALIDATE_BOOL);

            if (! $optIn) {
                return;
            }
        }

        $lists = $this->resolveLists($config, $submission);
        $email = $submission->get($config->get('email_field'));
        $attributes = collect($config->get('attribute_fields'))
            ->map(fn ($item) => [$item['attribute'] => $submission->get($item['field'])])
            ->collapse()
            ->all();

        $useDoubleOptIn = $config->get('use_double_opt_in', false);

        if ($useDoubleOptIn) {
            $template = $config->get('template');
            $redirectionUrl = $config->get('redirection_url');

            $this->createUsingDoubleOptIn(
                lists: $lists,
                email: $email,
                attributes: $attributes,
                template: $template,
                redirectionUrl: $redirectionUrl,
            );

            return;
        }

        $this->create(
            lists: $lists,
            email: $email,
            attributes: $attributes
        );
    }

    protected function resolveLists(Collection $config, Submission $submission): array
    {
        $lists = collect($config->get('lists'));

        if ($config->get('dynamic_lists', false)) {
            $lists = $lists->merge(
                collect($config->get('conditional_lists'))
                    ->filter(fn ($rule) => $this->matchesRule($rule, $submission))
                    ->flatMap(fn ($rule) => $rule['lists'] ?? [])
            );
        }

        return $lists->unique()->values()->all();
    }

    protected function matchesRule(array $rule, Submission $submission): bool
    {
        $value = $submission->get($rule['field'] ?? null);
        $expected = $rule['value'] ?? null;

        if ($expected === null || $expected === '') {
            return is_array($value)
                ? ! empty($value)
                : filter_var($value, FILTER_VALIDATE_BOOL);
        }

        return is_array($value)
            ? in_array($expected, array_map(strval(...), $value), true)
            : (string) $value === (string) $expected;
    }
}
