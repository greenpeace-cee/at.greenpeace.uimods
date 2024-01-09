<?php

class CRM_Uimods_Utils_OptionValue {

  /**
   * @param $optionGroupName
   * @param $optionValueName
   * @return bool
   */
  public static function isExist($optionGroupName, $optionValueName) {
    if (empty($optionGroupName) || empty($optionValueName)) {
      return FALSE;
    }

    try {
      $optionValues = civicrm_api3('OptionValue', 'get', [
        'name' => $optionValueName,
        'option_group_id' => $optionGroupName,
        'limit' => 1
      ]);
    } catch (\CiviCRM_API3_Exception $e) {
      return FALSE;
    }

    if (empty($optionValues['values'])) {
      return FALSE;
    }

    foreach ($optionValues['values'] as $entity) {
      return TRUE;
    }

    return FALSE;
  }

}
