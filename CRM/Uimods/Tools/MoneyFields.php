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
          if (!empty($fieldValue) && !preg_match('/^\-?\d+' . preg_quote(\Civi::settings()->get('monetaryDecimalPoint')) . '?\d?\d?$/', $fieldValue)) {
            $errors[$fieldName] = 'Please use the format "1234' . \Civi::settings()->get('monetaryDecimalPoint') . '56".';
          }
        }
      }
    }
  }

  /**
   * Process BuildForm hook
   *
   * @param $formName
   * @param $form
   * @return void
   */
  public static function processBuildForm($formName, &$form) {
    if (empty($formName) || empty($form)) {
      return;
    }

    foreach ($form->_rules as $fieldName => $fieldRules) {
      foreach ($fieldRules as $fieldRule) {
        if (!empty($fieldRule['type']) && $fieldRule['type'] === 'money') {
          $monetaryDecimalPoint = \Civi::settings()->get('monetaryThousandSeparator');

          if ($form->elementExists($fieldName)) {
            $element = $form->getElement($fieldName);
            $value = $element->getValue();
            $cleanValue = str_replace($monetaryDecimalPoint, "", $value);
            $element->setValue($cleanValue);
          }
        }
      }
    }
  }

}
