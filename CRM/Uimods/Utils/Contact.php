<?php

class CRM_Uimods_Utils_Contact {

  /**
   * @param $contactId
   * @return false
   */
  public static function isExist($contactId) {
    if (empty($contactId)) {
      return false;
    }

    $contacts = \Civi\Api4\Contact::get()
      ->addSelect('id')
      ->addWhere('id', '=', $contactId)
      ->setLimit(1)
      ->execute();

    foreach ($contacts as $contact) {
      return true;
    }

    return false;
  }

  public static function isContactDialoger(int $contactId): bool {
    if (empty($contactId)) {
      return false;
    }

    $contact = \Civi\Api4\Contact::get()
      ->addSelect('id')
      ->addWhere('id', '=', $contactId)
      ->addWhere('contact_sub_type', '=', 'Dialoger')
      ->setLimit(1)
      ->execute()
      ->first();

    if (!empty($contact)) {
      return true;
    }

    return false;
  }


}
