<?php

namespace Civi\Uimods\Hooks\ValidateForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use CRM_Case_Form_Activity;
use CRM_Uimods_ExtensionUtil as E;

class ValidateSubjectAfterChangeCaseStatus extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_validateForm' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->formName !== CRM_Case_Form_Activity::class) {
      return;
    }

    if (empty($event->fields['subject'])) {
      return;
    }

    $caseId = (int) $event->form->_caseId[0];
    $newCaseStatusId = (string) $event->fields['case_status_id'];
    $oldCaseStatusId = ValidateSubjectAfterChangeCaseStatus::getCaseStatusId($caseId);

    if ($newCaseStatusId === $oldCaseStatusId) {
      return;
    }

    if (empty($event->errors)) {
      $event->errors = [];
    }

    $event->errors['subject'] = E::ts('Subject have to be empty. It will be automatically-generated. See at Activity Details.');
  }

  public static function getCaseStatusId(int $caseId): string {
    $case = \Civi\Api4\CiviCase::get(FALSE)
      ->addSelect('status_id')
      ->addWhere('id', '=', 3)
      ->setLimit(1)
      ->execute()
      ->first();

    if (!empty($case)) {
      return (string) $case['status_id'];
    }

    return '';
  }

}
