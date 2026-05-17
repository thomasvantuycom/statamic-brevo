<?php

namespace ThomasVantuycom\StatamicBrevo\Listeners;

use Statamic\Events\SubmissionCreated;
use ThomasVantuycom\StatamicBrevo\Facades\Contact;

class CreateContactFromSubmission
{
    public function handle(SubmissionCreated $event)
    {
        $submission = $event->submission;
        $form = $event->submission->form();

        if ($form->has('brevo')) {
            Contact::createFromSubmission($submission);
        }
    }
}
