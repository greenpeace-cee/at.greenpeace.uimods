<?php

namespace Civi\Uimods\Hooks\Pre;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\Utils\BirthYearService;

class BirthYear extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_post' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if ($event->entity !== 'Individual') {
      return;
    }


    $isBirthDateFiledExist = property_exists($event->object, 'birth_date');

    if (!$isBirthDateFiledExist && (!empty($event->object->id) || !empty($event->object->contact_id))) {
      BirthYearService::forbidToUpdateBirthYear();
    }
  }

}
