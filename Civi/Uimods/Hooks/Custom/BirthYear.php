<?php

namespace Civi\Uimods\Hooks\Custom;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\Utils\BirthYearService;
use CRM_Utils_Request;
use Exception;
use DateTime;

class BirthYear extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_custom' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    $className = CRM_Utils_Request::retrieve('class_name', 'String');

    if ($className != 'CRM_Contact_Form_Inline_CustomData') {
      return;
    }

    $birthYearField = BirthYearService::getBirthYearCustomField();
    $groupID = CRM_Utils_Request::retrieve('groupID', 'Positive');

    if ($birthYearField['custom_group_id'] != $groupID) {
      return;
    }

    // deletes the values CiviCRM's Birth Date field if someone updates the custom field with a year that is contradictory to the birth date
    foreach ($event->params as $entity) {
      if (empty($entity['entity_table'])) {
        continue;
      }

      if ($entity['entity_table'] !== 'civicrm_contact') {
        continue;
      }

      if ($birthYearField['column_name'] !== $entity['column_name']) {
        continue;
      }

      if ($birthYearField['custom_group_id'] !== $entity['custom_group_id']) {
        continue;
      }

      // birth_year field was written
      // Get value of birth_year field
      $customValues = civicrm_api3('CustomValue', 'get', [
        'entity_id' => $entity['entity_id'],
        'return.custom_' . $birthYearField['id'] => 1,
      ]);
      $birthYear = $customValues['values'][$birthYearField['id']][0];

      // Get contact ID birth date field ($event->params['entity_id'])
      try {
        $contactBirthDate = civicrm_api3('Contact', 'getsingle', [
          'return' => "birth_date",
          'id' => $entity['entity_id'],
        ]);
      }
      catch (Exception $e) {
        //getsingle throws exception if not found
        return;
      }
      // Contact birth date to year
      if (!empty($contactBirthDate['birth_date'])) {
        try {
          $contactBirthYear = new DateTime($contactBirthDate['birth_date']);
        }
        catch (Exception $e) {
          return;
        }

        // Is birth date = birth year? (Match only long format)
        if ($contactBirthYear->format('Y') != $birthYear) {
          BirthYearService::forbidToUpdateBirthYear();
          BirthYearService::clearBirthDate($entity['entity_id']);
          BirthYearService::allowToUpdateBirthYear();
        }
      }
    }
  }

}
