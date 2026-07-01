<?php

namespace Civi\Uimods\Hooks\PageRun;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\AssetInjector;
use CRM_Contact_Page_View_Summary;
use CRM_Contact_Page_Inline_Email;

class SupportcaseLinks extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_pageRun' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if (!in_array(get_class($event->page), [
      CRM_Contact_Page_View_Summary::class,
      CRM_Contact_Page_Inline_Email::class
    ])) {
      return;
    }

    AssetInjector::addScript('js/supportcaseLinks.js');
    AssetInjector::addCssStyles('css/supportcaseLinks.css');
  }

}
