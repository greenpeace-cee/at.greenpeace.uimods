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

    if (empty($event->object->birth_date)) {
      return;
    }

    try {
      $newBirtDateDateTime = new DateTime($event->object->birth_date);
    } catch (Exception $e) {
      return;
    }

    BirthYearService::saveBirthYear($event->object->id, $newBirtDateDateTime->format('Y'));
  }

}
