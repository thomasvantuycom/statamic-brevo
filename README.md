# Brevo addon for Statamic

Create Brevo contacts from Statamic form submissions.

This addon adds a Brevo configuration section to Statamic forms. When a configured form is submitted, the addon can create or update a Brevo contact, add the contact to one or more lists, map Statamic form fields to Brevo contact attributes, and optionally require an opt-in field or Brevo double opt-in flow.

## Requirements

- Statamic 6.0.0 or above
- A Brevo API key

## Installation

Install the addon with Composer:

```bash
composer require thomasvantuycom/statamic-brevo
```

Add your Brevo API key to your environment:

```env
BREVO_API_KEY=your-api-key
```

## Usage

Open a form in the Statamic control panel. The addon adds a **Brevo** section to the form configuration.

Enable **Create Contacts**, then configure:

- **Lists**: the Brevo lists the contact should be added to.
- **Dynamic Lists**: optionally add contacts to lists based on what they submitted.
- **Email Field**: the Statamic form field containing the contact email address.
- **Attribute Fields**: optional mappings from Statamic form fields to Brevo contact attributes.
- **Opt-in Field**: optional field that must evaluate to true before a contact is created.

When the form is submitted, the addon sends the contact to Brevo. Existing contacts are updated by using Brevo's `updateEnabled` option.

## Attribute Mapping

Use **Attribute Fields** to map submitted form values to Brevo contact attributes.

For example:

| Form field | Brevo attribute |
| --- | --- |
| `first_name` | `FIRSTNAME` |
| `last_name` | `LASTNAME` |

Attributes are optional. If no attributes are configured, the contact is still created with the selected lists and email address.

## Dynamic Lists

Enable **Dynamic Lists** to add contacts to different lists depending on what they submitted, for example when a form lets people pick which newsletters they want.

With dynamic lists enabled, **Lists** becomes optional. Contacts are always added to the lists selected there, so leave it empty when the lists should be determined by the submission alone.

Each rule under **Conditional Lists** maps a form field and an expected value to one or more lists:

| Field | Value | Lists |
| --- | --- | --- |
| `topics` | `banking` | Banking newsletter |
| `topics` | `payments` | Payments newsletter |
| `role` | `partner` | Partner updates |

Rules are evaluated against the submission:

- If the submitted value is an array, such as a checkboxes field, the rule matches when the value is one of the selected options. Selecting both `banking` and `payments` therefore adds the contact to both newsletters.
- If the submitted value is a single value, the rule matches when it equals the configured value.
- If the value is left blank, the rule matches whenever the field is truthy or a non-empty array. This is useful for toggles.

All matching rules are combined with the lists selected in **Lists**, and duplicates are removed. Dynamic lists apply to both regular and double opt-in contacts.

If an **Opt-in Field** is configured, the addon checks the submitted value before creating the contact.

If the value is false, empty, or otherwise not considered truthy, no Brevo request is sent. Leave the opt-in field blank when consent is handled outside this form or is not required for the form.

## Double Opt-in

Enable **Use Double Opt-in** to use Brevo's double opt-in contact flow instead of creating the contact immediately.

When double opt-in is enabled, configure:

- **Template**: the Brevo double opt-in template to send.
- **Redirection URL**: where the contact should be redirected after confirming.

The selected template must be a valid Brevo double opt-in template.

## License

MIT
