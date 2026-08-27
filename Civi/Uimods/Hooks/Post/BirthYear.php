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
    if (!($event->object instanceof CRM_Contact_DAO_Contact)) {
      return;
    }

    if (!BirthYearService::isAllowToUpdateBirthYear()) {
      return;
    }

    try {
      $birtDateDateTime = new DateTime($event->object->birth_date);
    } catch (Exception $e) {
      return;
    }

    if (!empty($event->object->birth_date) && $event->object->birth_date != 'null') {
      BirthYearService::saveBirthYear($event->object->id, $birtDateDateTime->format('Y'));
      return;
    }

    $birthDate = BirthYearService::getBirthDateFieldValue($event->object->id);
    if (empty($birthDate)) {
      return;
    }

    $birthYear = BirthYearService::getBirthYearFieldValue($event->object->id);
    if (empty($birthYear)) {
      return;
    }

    if ($birtDateDateTime->format('Y') == $birthYear) {
      return;
    }

    BirthYearService::forbidToUpdateBirthYear();
    BirthYearService::clearBirthDate($event->object->id);
    BirthYearService::allowToUpdateBirthYear();
  }

}
