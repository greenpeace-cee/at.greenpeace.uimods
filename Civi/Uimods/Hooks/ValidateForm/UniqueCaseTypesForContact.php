<?php

namespace Civi\Uimods\Hooks\ValidateForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use CRM_Case_Form_Case;
use CRM_Core_Action;
use CRM_Uimods_Utils_Contact;
use CRM_Uimods_ExtensionUtil as E;

class UniqueCaseTypesForContact extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_validateForm' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->formName !== CRM_Case_Form_Case::class) {
      return;
    }

    if (!in_array($event->form->getAction(), [
      CRM_Core_Action::UPDATE,
      CRM_Core_Action::ADD
    ])) {
      return;
    }

    $clientId = $event->fields['client_id'];
    $caseTypeId = $event->fields['case_type_id'];
    $uniqueCaseTypeIds = UniqueCaseTypesForContact::getUniqueCaseTypeIds();
    if (!in_array($caseTypeId, $uniqueCaseTypeIds)) {
      return;
    }

    $cases = \Civi\Api4\CiviCase::get(FALSE)
      ->addSelect('id', 'contact.*')
      ->addJoin(
        'CaseContact AS contact',
        'LEFT',
        ['contact.case_id', '=', 'id']
      )
      ->addWhere('case_type_id', '=', $caseTypeId)
      ->addWhere('contact.contact_id', '=', $clientId)
      ->execute();

    $caseIds = [];
    foreach ($cases as $case) {
      $caseIds[] = $case['id'];
    }

    if (empty($caseIds)) {
      return;
    }

    $event->errors['case_type_id'] = E::ts('Cannot create case. This contact already has case with this type! See cases id=[: ' . implode(',', $caseIds) . ']');
  }

  public static function getUniqueCaseTypeIds(): array
  {
    $caseTypeNames = Civi::settings()->get('unique_case_types');
    if (empty($caseTypeNames)) {
      return [];
    }

    $caseTypes = \Civi\Api4\CaseType::get(FALSE)
      ->addWhere('name', 'IN', $caseTypeNames)
      ->addSelect('id')
      ->execute();

    $caseTypeIds = [];
    foreach ($caseTypes as $caseType) {
      $caseTypeIds[] = $caseType['id'];
    }

    return $caseTypeIds;
  }

}
