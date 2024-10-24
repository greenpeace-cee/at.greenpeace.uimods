<?php
/*-------------------------------------------------------+
| Greenpeace UI Modifications                            |
| Copyright (C) 2017 SYSTOPIA                            |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


/**
 * Minor Changes to the UI Merge form (GP-1090)
 *  -
 */
class CRM_Uimods_MergeFromUIMods {

  /**
   * Page run hook for UI Merge
   * @param $formName
   * @param $form
   */
  public static function buildFormHook($formName, &$form) {

    CRM_Core_Resources::singleton()->addStyleFile('at.greenpeace.uimods', 'css/merge.css');

    $script = file_get_contents(__DIR__ . '/../../js/merge_form_mods.js');
    $hiddenCustomFields = [];

    try {
      // fields from the sumfields extensions aren't relevant, so we hide them
      $result = civicrm_api3('CustomField', 'get', [
          'custom_group_id' => 'Summary_Fields'
      ]);
      foreach ($result['values'] as $customField) {
        $hiddenCustomFields[] = 'custom_' . $customField['id'];
      }
    } catch (Exception $ex) {
      // probably just not installed...
      // Remark: I know I could've removed the whole JS, but maybe there'll be
      //   fields to hide in the future beyond SummaryFields
    }

    CRM_Core_Resources::singleton()->addVars('hiddenCustomFields', $hiddenCustomFields);
    CRM_Core_Region::instance('page-footer')->add(array(
      'script' => $script,
    ));

  }
}
