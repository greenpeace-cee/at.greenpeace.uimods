<?php

class CRM_Uimods_Tools_DialogerId {

  /**
   * Process validateForm hook
   *
   * @param $formName
   * @param $fields
   * @param $files
   * @param $form
   * @param $errors
   */
  public static function processValidateForm($formName, &$fields, &$files, &$form, &$errors) {
    if (!in_array($formName, ['CRM_Contact_Form_Inline_CustomData', 'CRM_Contact_Form_Contact'])) {
      return;
    }

    if (!in_array($form->getAction(), [CRM_Core_Action::UPDATE, CRM_Core_Action::ADD])) {
      return;
    }

    if ($form->getAction() === CRM_Core_Action::ADD && $formName === 'CRM_Contact_Form_Inline_CustomData') {
      return;
    }

    if ($form->getAction() === CRM_Core_Action::ADD) {
      $contactSubType = CRM_Utils_Request::retrieve('cst', 'String', $form);
      if ($contactSubType !== 'Dialoger') {
        return;
      }
    }

    $dialogerIdFieldName = CRM_Core_BAO_CustomField::getCustomFieldID('dialoger_id', 'dialoger_data', TRUE);
    if (empty($dialogerIdFieldName)) {
      return;
    }

    $dialogerIdKey = null;
    $dialogerIdNewValue = null;
    foreach ($fields as $key => $value) {
      if (preg_match("#^{$dialogerIdFieldName}_-?[0-9]+$#", $key)) {
        $dialogerIdKey = $key;
        $dialogerIdNewValue = $value;
      }
    }

    if (empty($dialogerIdKey) || empty($dialogerIdNewValue)) {
      return;
    }

    $contactsWithSameDialogerId = self::findContacts($dialogerIdNewValue);
    if (empty($contactsWithSameDialogerId)) {
      return;
    }

    if ($form->getAction() === CRM_Core_Action::UPDATE) {
      $contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $form);
      if (empty($contactId)) {
        return;
      }

      if (count($contactsWithSameDialogerId) === 1 && $contactsWithSameDialogerId[0]['id'] === $contactId) {
        return;
      }
    }

    $errorMessage = '<br>The filed isn\'t unique. The same value has contacts: ';
    $linkStyles = 'display: block; color: #0071bd; background: rgba(255, 255, 255, 0);padding: 5px 0';
    $errorMessage .= '<ul>';
    foreach ($contactsWithSameDialogerId as $contact) {
      $errorMessage .= '<li>';
      $isCurrentContact = $form->getAction() === CRM_Core_Action::UPDATE && $contact['id'] === $contactId;
      $link = CRM_Utils_System::url('civicrm/contact/view', "reset=1&amp;cid={$contact['id']}");
      $errorMessage .= '<a style="' . $linkStyles . '" href="' . $link . '">';
      $errorMessage .= $contact['display_name'] . '(id=' . $contact['id'] . ($isCurrentContact ? ', current contact' : '') . ');';
      $errorMessage .= '</a>';
      $errorMessage .= '</li>';
    }
    $errorMessage .= '</ul>';

    $errors[$dialogerIdKey] = $errorMessage;
  }

  /**
   * Finds contacts by 'DialogerId' custom field
   *
   * @param $dialogerIdValue
   * @return array
   */
  public static function findContacts($dialogerIdValue) {
    $contacts = \Civi\Api4\Contact::get()
      ->addSelect('dialoger_data.dialoger_id', 'id', 'display_name')
      ->addWhere('dialoger_data.dialoger_id', '=', $dialogerIdValue)
      ->setLimit(0)
      ->execute();

    $preparedContacts = [];
    foreach ($contacts as $contact) {
      $preparedContacts[] = [
        'id' => $contact['id'],
        'dialoger_id' => $contact['dialoger_data.dialoger_id'],
        'display_name' => $contact['display_name'],
      ];
    }

    return $preparedContacts;
  }

}
