<?php

return [
    'form_configure_brevo_enabled_instructions' => 'Automatically create Brevo contacts from form submissions.',
    'form_configure_brevo_lists_instructions' => 'The lists that the contact should be added to. With dynamic lists enabled this is optional, and contacts are always added to these lists.',
    'form_configure_brevo_dynamic_lists_instructions' => 'Add contacts to lists based on what they submitted, on top of the lists above.',
    'form_configure_brevo_conditional_lists_instructions' => 'Add the contact to additional lists based on submitted values. Leave the value blank to match any truthy value.',
    'form_configure_brevo_email_field_instructions' => 'The form field that contains the contact’s email address.',
    'form_configure_brevo_attribute_fields_instructions' => 'Map form fields to contact attributes.',
    'form_configure_brevo_opt_in_field_instructions' => 'The form field that indicates the contact’s consent. If no consent is given, the contact will not be created. Leave blank if consent is not required.',
    'form_configure_brevo_use_double_opt_in_instructions' => 'Require contacts to confirm their subscription before being added to lists.',
    'form_configure_brevo_template_instructions' => 'The template for the double opt-in confirmation email.',
    'form_configure_brevo_redirection_url_instructions' => 'The URL to redirect contacts to after confirming their subscription.',
];
