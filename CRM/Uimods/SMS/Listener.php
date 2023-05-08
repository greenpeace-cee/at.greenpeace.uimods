<?php

use Civi\Api4\Contact;
use Civi\Api4\Phone;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Event\PreEvent;

class CRM_Uimods_SMS_Listener {

  public static array $newPhones = [];

  /**
   * Process Phone entity pre events
   *
   * @param \Civi\Core\Event\PreEvent $event
   */
  public static function pre(PreEvent $event) {
    if ($event->action != 'create' || $event->entity != 'Phone') {
      return;
    }
    if (empty($event->params['phone'])) {
      return;
    }
    if (!in_array($event->params['phone'], self::$newPhones)) {
      return;
    }
    // this is a new phone. should we update location_type_id?
    if (!empty(\Civi::settings()->get('at_greenpeace_uimods_sms_default_location_type_id'))) {
      // default location type for new phones is set, overwrite it
      $event->params['location_type_id'] = \Civi::settings()->get('at_greenpeace_uimods_sms_default_location_type_id');
    }
    $key = array_search($event->params['phone'], self::$newPhones);
    unset(self::$newPhones[$key]);
  }

  /**
   * Process inboundSMS events
   *
   * This roughly reimplements the contact matcher in CRM_SMS_Provider:::processInbound,
   * with some changes:
   *  - uses APIv4
   *  - matching contacts are explicitly ordered by lowest contact_id first
   *  - the phone number is used as the display_name, rather than adding a fake
   *    <phone>@mobile.sms email
   *
   * @param \Civi\Core\Event\GenericHookEvent $event
   *
   * @throws \Exception
   */
  public static function inboundSMS(GenericHookEvent $event) {
    $message = $event->message;
    $message->fromContactID = self::getOrCreateContact($message->from);
    $session = CRM_Core_Session::singleton();
    $userId = $session->get('userID');
    if (empty($userId)) {
      $session->set('userID', $message->fromContactID);
    }
    $message->toContactID = self::getOrCreateContact($message->to);
  }

  public static function getOrCreateContact($phone) {
    $contact = Phone::get()
      ->addSelect('contact_id')
      ->addWhere('phone', '=', $phone)
      ->addWhere('contact_id.is_deleted', '=', FALSE)
      ->addOrderBy('contact_id', 'ASC')
      ->setCheckPermissions(FALSE)
      ->execute()
      ->first();

    if (!empty($contact)) {
      // matched to existing contact
      return $contact['contact_id'];
    }
    else {
      if (\Civi::settings()->get('at_greenpeace_uimods_sms_discard_unknown_sender')) {
        // we don't really have a good way to "discard" SMS from this hook
        // other than to just ... exit.
        Civi::log()->info('Discarding SMS from unknown sender: ' . $phone);
        CRM_Utils_System::civiExit();
      }
      // we want to process inbound SMS from unknown numbers. create a contact
      // using the phone number as the display name

      // remember phone for pre hook
      self::$newPhones[] = $phone;
      // create a new contact
      $contact = Contact::create(FALSE)
        ->addValue('display_name', $phone)
        ->addChain('Phone', Phone::create()
          ->addValue('contact_id', '$id')
          ->addValue('phone', $phone)
        )
        ->execute()
        ->single();
      return $contact['id'];
    }
  }

}
