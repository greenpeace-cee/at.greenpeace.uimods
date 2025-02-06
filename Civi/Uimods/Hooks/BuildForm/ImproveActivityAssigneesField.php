<?php

namespace Civi\Uimods\Hooks\BuildForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;

class ImproveActivityAssigneesField {

  public static function run(GenericHookEvent $event): void {
    if (!self::isNeedToRun($event)) {
      return;
    }

    $elementName = 'assignee_contact_id';
    if (!$event->form->elementExists($elementName)) {
      return;
    }

    $groupName = Civi::settings()->get('at_greenpeace_uimods_activity_assignees_filter_group_name');
    if (empty($groupName)) {
      return;
    }

    $element = $event->form->getElement($elementName);
    $dataApiParamsJson = $element->getAttribute('data-api-params');
    $dataApiParams = json_decode($dataApiParamsJson, true);
    $dataApiParams['params']['group'] = $groupName;
    $updatedDataApiParamsJson = json_encode($dataApiParams);
    $element->setAttribute('data-api-params', $updatedDataApiParamsJson);
  }

  private static function isNeedToRun(GenericHookEvent $event): bool {
    if (!in_array($event->formName, ['CRM_Activity_Form_Activity', 'CRM_Fastactivity_Form_Add'])) {
      return false;
    }

    return true;
  }

}
