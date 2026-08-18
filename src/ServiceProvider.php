<?php

namespace ThomasVantuycom\StatamicBrevo;

use Brevo\Brevo;
use GuzzleHttp\Client;
use Statamic\Facades\Form;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        $this
            ->registerBrevoClient();
    }

    protected function registerBrevoClient()
    {
        $this->app->singleton(Brevo::class, function () {
            return new Brevo(
                apiKey: config('statamic.brevo.key'),
                options: [
                    'client' => new Client,
                ]
            );
        });

        return $this;
    }

    public function bootAddon()
    {
        $this
            ->bootConfig()
            ->bootFormConfigFields();
    }

    protected function bootConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/brevo.php', 'statamic.brevo');

        $this->publishes([
            __DIR__.'/../config/brevo.php' => config_path('statamic/brevo.php'),
        ], 'brevo-config');

        return $this;
    }

    protected function bootFormConfigFields()
    {
        Form::appendConfigFields('*', __('Brevo'), [
            'brevo' => [
                'type' => 'brevo_form_config',
                'hide_display' => true,
                'full_width_setting' => true,
                'fullscreen' => false,
                'fields' => [
                    [
                        'handle' => 'enabled',
                        'field' => [
                            'type' => 'toggle',
                            'display' => __('Create Contacts'),
                            'instructions' => __('brevo::messages.form_configure_brevo_enabled_instructions'),
                        ],
                    ],
                    [
                        'handle' => 'dynamic_lists',
                        'field' => [
                            'type' => 'toggle',
                            'display' => __('Dynamic Lists'),
                            'instructions' => __('brevo::messages.form_configure_brevo_dynamic_lists_instructions'),
                            'if' => ['enabled' => true],
                        ],
                    ],
                    [
                        'handle' => 'lists',
                        'field' => [
                            'type' => 'brevo_lists',
                            'display' => __('Lists'),
                            'instructions' => __('brevo::messages.form_configure_brevo_lists_instructions'),
                            'multiple' => true,
                            'if' => ['enabled' => true],
                            'validate' => ['sometimes', 'required_unless:brevo.dynamic_lists,true'],
                        ],
                    ],
                    [
                        'handle' => 'conditional_lists',
                        'field' => [
                            'type' => 'grid',
                            'display' => __('Conditional Lists'),
                            'instructions' => __('brevo::messages.form_configure_brevo_conditional_lists_instructions'),
                            'mode' => 'table',
                            'add_row' => __('Add Rule'),
                            'fullscreen' => false,
                            'full_width_setting' => true,
                            'if' => ['enabled' => true, 'dynamic_lists' => true],
                            'fields' => [
                                [
                                    'handle' => 'field',
                                    'field' => [
                                        'display' => __('Field'),
                                        'type' => 'form_field',
                                        'width' => 33,
                                        'validate' => ['required'],
                                    ],
                                ],
                                [
                                    'handle' => 'value',
                                    'field' => [
                                        'display' => __('Value'),
                                        'type' => 'text',
                                        'width' => 33,
                                    ],
                                ],
                                [
                                    'handle' => 'lists',
                                    'field' => [
                                        'display' => __('Lists'),
                                        'type' => 'brevo_lists',
                                        'multiple' => true,
                                        'width' => 33,
                                        'validate' => ['required'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'handle' => 'email_field',
                        'field' => [
                            'type' => 'form_field',
                            'display' => __('Email Field'),
                            'instructions' => __('brevo::messages.form_configure_brevo_email_field_instructions'),
                            'if' => ['enabled' => true],
                            'validate' => ['sometimes', 'required'],
                        ],
                    ],
                    [
                        'handle' => 'attribute_fields',
                        'field' => [
                            'type' => 'grid',
                            'display' => __('Attribute Fields'),
                            'instructions' => __('brevo::messages.form_configure_brevo_attribute_fields_instructions'),
                            'mode' => 'table',
                            'add_row' => __('Add Field'),
                            'fullscreen' => false,
                            'full_width_setting' => true,
                            'if' => ['enabled' => true],
                            'fields' => [
                                [
                                    'handle' => 'field',
                                    'field' => [
                                        'display' => __('Field'),
                                        'type' => 'form_field',
                                        'width' => 50,
                                        'validate' => ['required'],
                                    ],
                                ],
                                [
                                    'handle' => 'attribute',
                                    'field' => [
                                        'display' => __('Attribute'),
                                        'type' => 'brevo_attributes',
                                        'width' => 50,
                                        'validate' => ['required'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'handle' => 'opt_in_field',
                        'field' => [
                            'type' => 'form_field',
                            'display' => __('Opt-in Field'),
                            'instructions' => __('brevo::messages.form_configure_brevo_opt_in_field_instructions'),
                            'if' => ['enabled' => true],
                        ],
                    ],
                    [
                        'handle' => 'use_double_opt_in',
                        'field' => [
                            'type' => 'toggle',
                            'display' => __('Use Double Opt-in'),
                            'instructions' => __('brevo::messages.form_configure_brevo_use_double_opt_in_instructions'),
                            'if' => ['enabled' => true],
                        ],
                    ],
                    [
                        'handle' => 'template',
                        'field' => [
                            'type' => 'brevo_templates',
                            'display' => __('Template'),
                            'instructions' => __('brevo::messages.form_configure_brevo_template_instructions'),
                            'if' => ['enabled' => true, 'use_double_opt_in' => true],
                            'validate' => ['sometimes', 'required'],
                        ],
                    ],
                    [
                        'handle' => 'redirection_url',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Redirection URL'),
                            'instructions' => __('brevo::messages.form_configure_brevo_redirection_url_instructions'),
                            'input_type' => 'url',
                            'if' => ['enabled' => true, 'use_double_opt_in' => true],
                            'validate' => ['sometimes', 'required', 'url'],
                        ],
                    ],
                ],
            ],
        ]);

        return $this;
    }
}
