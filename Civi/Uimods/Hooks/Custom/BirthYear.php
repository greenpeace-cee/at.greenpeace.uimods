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

    // deletes the values CiviCRM's Birth Date field if someone updates the custom field
    // with a year that is contradictory to the birth date
    foreach ($event->params as $entity) {
      if (empty($entity['entity_table'])) {
        continue;
      }

      if ($entity['entity_table'] !== 'civicrm_contact') {
        continue;
      }

      if ((int) $birthYearField['id'] != (int) $entity['custom_field_id']) {
        continue;
      }

      $birthYear = BirthYearService::getBirthYearFieldValue($entity['entity_id']);
      $birthDate = BirthYearService::getBirthDateFieldValue($entity['entity_id']);
      if (empty($birthDate)) {
        return;
      }

      try {
        $birthYearDateTime = new DateTime($birthDate);
      } catch (Exception $e) {
        return;
      }

      if ($birthYearDateTime->format('Y') == $birthYear) {
        return;
      }

      BirthYearService::forbidToUpdateBirthYear();
      BirthYearService::clearBirthDate($entity['entity_id']);
      BirthYearService::allowToUpdateBirthYear();
    }
  }

}
