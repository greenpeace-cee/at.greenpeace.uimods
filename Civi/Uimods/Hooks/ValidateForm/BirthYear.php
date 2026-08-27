<?php

namespace Civi\Uimods\Hooks\ValidateForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\Utils\BirthYearService;
use Exception;
use DateTime;

class BirthYear extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_validateForm' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->formName !== 'CRM_Contact_Form_Contact') {
      return;
    }

    $birthYearField = BirthYearService::getBirthYearCustomField();
    $birthYearElementName = $event->form->_groupTree[$birthYearField['custom_group_id']]['fields'][$birthYearField['id']]['element_name'];

    if (empty($event->fields['birth_date']) || empty($event->fields[$birthYearElementName])) {
      return;
    }

    try {
      $contactBirthYear = (new DateTime($event->fields['birth_date']))->format('Y');
    } catch (Exception $e) {
      return;
    }

    if ($contactBirthYear != $event->fields[$birthYearElementName]) {
      $event->errors[$birthYearElementName] = ts('The Year of Birth should be the same as Birth Date');
      $event->errors['birth_date'] = ts('The Birth Date should be the same as Year of Birth');
    }
  }

}
