<?php

class CRM_Uimods_Tools_MoneyFields {

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
    if (empty($fields) || empty($form) || empty($form->_rules)) {
      return;
    }

    foreach ($form->_rules as $fieldName => $fieldRules) {
      foreach ($fieldRules as $fieldRule) {
        if (!empty($fieldRule['type']) && $fieldRule['type'] === 'money' && isset($fields[$fieldName])) {
          $fieldValue = $fields[$fieldName];
          if (CRM_Uimods_Tools_String::isStringContains(',', $fieldValue)) {
            $errors[$fieldName] = 'The "," symbol is invalid. Use "." instead of ",".';
          }
        }
      }
    }
  }

}
