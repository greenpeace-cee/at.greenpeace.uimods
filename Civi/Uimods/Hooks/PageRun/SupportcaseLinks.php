<?php

namespace Civi\Uimods\Hooks\PageRun;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use Civi\Uimods\AssetInjector;
use CRM_Contact_Page_View_Summary;

class SupportcaseLinks extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_pageRun' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void {
    if (get_class($event->page) !== CRM_Contact_Page_View_Summary::class) {
      return;
    }

    AssetInjector::addScriptInline('js/supportcaseLinks.js');
    AssetInjector::addCssStyles('css/supportcaseLinks.css');
  }

}
