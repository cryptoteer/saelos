<?php

namespace App\Subscribers;

use App\Activity;
use App\CallActivity;
use App\EmailActivity;
use App\Events\ContactEmailed;
use App\Contact;
use App\User;
use Treblig\Plivo\Laravel\Events\CallAnswered;
use Treblig\Plivo\Laravel\Events\CallEnded;
use Treblig\Plivo\Laravel\Events\CallInitiated;
use Treblig\Plivo\Laravel\Events\RecordingReceived;

/**
 * Class ContactSubscriber
 *
 * @package App\Subscribers
 */
class ContactSubscriber
{
    public function subscribe($events)
    {
        $events->listen(
            ContactEmailed::class,
            __CLASS__.'@onContactEmailed'
        );
    }

    public function onContactEmailed(ContactEmailed $event)
    {
        $contact = $event->getContact();
        $user = $event->getUser();
        $email = $event->getEmail();

        $details = EmailActivity::create([
            'content' => $email->getEmailContent(),
            'details' => []
        ]);

        $activity = Activity::create([
            'title' => 'Email sent.',
            'description' => sprintf(
                'Email to %s %s sent by %s.',
                $contact->first_name,
                $contact->last_name,
                $user->name
            ),
            'user_id' => $user->id,
            'details_id' => $details->id,
            'details_type' => EmailActivity::class
        ]);

        $contact->activities()->save($activity, ['primary' => true]);
    }
}