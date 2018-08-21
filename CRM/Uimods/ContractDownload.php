<?php

/**
 * Contract numbers of anonymized contacts are kept to avoid accidental re-entry
 * of contract forms while delete requests are pending. To avoid showing the
 * form prior to the delete request being handled, explicitly hide the download
 * link for anonymized contacts.
 */
class CRM_Uimods_ContractDownload {
  public static function remove(&$form) {
    $contact_id = CRM_Utils_Request::retrieve('cid', 'Positive', $form);
    $display_name = civicrm_api3('Contact', 'getvalue', [
      'return' => 'display_name',
      'id' => $contact_id,
    ]);
    // anonymized contacts are identified by their display name
    if (strtolower($display_name) == 'anonymous') {
      CRM_Core_Region::instance('page-footer')->update(
        'contract-download@de.systopia.contract',
        ['disabled' => TRUE]
      );
    }
  }
}