<?php

namespace Civi\Uimods\Hooks\ValidateForm;

use Civi;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\Service\AutoSubscriber;
use CRM_Contact_Form_Merge;
use CRM_Uimods_Utils_Contact;
use CRM_Uimods_ExtensionUtil as E;

class PreventDialogerMerging extends AutoSubscriber {

  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_validateForm' => ['run', -20]];
  }

  public static function run(GenericHookEvent $event): void
  {
    if ($event->formName !== CRM_Contact_Form_Merge::class) {
      return;
    }

    $firstContactId = (int) $event->form->_cid;
    $secondContactId = (int) $event->form->_oid;
    $isFirstContactDialoger = CRM_Uimods_Utils_Contact::isContactDialoger($firstContactId);
    $isSecondContactDialoger = CRM_Uimods_Utils_Contact::isContactDialoger($secondContactId);

    if ($isFirstContactDialoger && $isSecondContactDialoger) {
      return;
    }

    if (!$isFirstContactDialoger && !$isSecondContactDialoger) {
      return;
    }

    if (empty($event->errors)) {
      $event->errors = [];
    }

    $event->errors['_qf_default'] = E::ts('Cannot merge contact! Contact with subtype "Dialoger" can be merged only with another "Dialoger".');
  }

}
