<?php

namespace Civi\Uimods\Hooks\BuildForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\AssetInjector;
use Civi\Uimods\Utils\BirthYearService;
use CRM_Contact_Form_Inline_CustomData;

class BirthYear extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_buildForm' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->formName !== CRM_Contact_Form_Inline_CustomData::class) {
      return;
    }

    $birthyearField = BirthYearService::getBirthYearCustomField();
    if (empty($birthyearField)) {
      return;
    }

    AssetInjector::addScriptInline('js/extended_demographics_edit.js', 'page-footer', ['BIRTH_YEAR_FIELD' => $birthyearField['id']]);
  }

}
