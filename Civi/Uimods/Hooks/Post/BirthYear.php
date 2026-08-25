<?php

namespace Civi\Uimods\Hooks\Post;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\Utils\BirthYearService;
use CRM_Contact_DAO_Contact;
use Exception;
use DateTime;

class BirthYear extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_post' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->object instanceof CRM_Contact_DAO_Contact) {
      if (!BirthYearService::isAllowToUpdateBirthYear()) {
        return;
      }

      if (!empty($event->object->birth_date) && $event->object->birth_date != 'null') {
        // Contact Birth date has a value
        try {
          // Contact birth date to (long) year
          $contactBirthYear = (new DateTime($event->object->birth_date))->format('Y');
        } catch (Exception $e) {
          return;
        }
      } else {
        $birthDate = civicrm_api3('Contact', 'getvalue', [
          'return' => 'birth_date',
          'id' => $event->object->id,
        ]);

        if (!empty($birthDate)) {
          $birthYearField = BirthYearService::getBirthYearCustomField();
          $customValues = civicrm_api3('CustomValue', 'get', array(
            'entity_id' => $event->object->id,
            'return.custom_' . $birthYearField['id'] => 1,
          ));

          if (empty($customValues['values'][$birthYearField['id']][0])) {
            return;
          }

          try {
            $contactBirthYear = (new DateTime($event->object->birth_date))->format('Y');
          } catch (Exception $e) {
            return;
          }

          if ($contactBirthYear == $customValues['values'][$birthYearField['id']][0]) {
            return;
          }

          BirthYearService::forbidToUpdateBirthYear();
          BirthYearService::clearBirthDate($event->object->id);
          BirthYearService::allowToUpdateBirthYear();

          return;
        }
      }

      if (empty($contactBirthYear)) {
        return;
      }

      $birthYearField = BirthYearService::getBirthYearCustomField();
      // Update birth year custom field with new value
      civicrm_api3('CustomValue', 'create', [
        'entity_id' => $event->object->id,
        "custom_{$birthYearField['id']}" => $contactBirthYear,
      ]);
    }
  }

}
