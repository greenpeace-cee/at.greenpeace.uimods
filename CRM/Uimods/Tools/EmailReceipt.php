<?php

class CRM_Uimods_Tools_EmailReceipt {

  /**
   * Disable and uncheck email receipt/notification checkboxes in various forms
   *
   * @param $formName
   * @param \CRM_Core_Form $form
   *
   * @return void
   */
  public static function process_buildForm($formName, CRM_Core_Form &$form) {
    $fields = [];
    switch ($formName) {
      case 'CRM_Event_Form_Participant':
        $fields[] = 'send_receipt';
        $fields[] = 'is_notify';
        break;

      case 'CRM_Contribute_Form_Contribution':
        $fields[] = 'is_email_receipt';
        break;
    }
    foreach ($fields as $field) {
      if ($form->elementExists($field)) {
        $formElement = $form->getElement($field);
        $formElement->setValue(FALSE);
        $formElement->setAttribute('disabled', TRUE);
      }
    }
  }

}
