<?php
/*-------------------------------------------------------+
| Greenpeace UI Modifications                            |
| Copyright (C) 2017 SYSTOPIA                            |
| Author    Matthew Wire (mjw@mjwconsult.co.uk)          |
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
 * Keep birth_date and birth year in sync
 */
class CRM_Uimods_Tools_BirthYear {

  /**
   * Get the birth year custom field
   */
  protected static $_birthyear_custom_field = NULL;

  /**
   * Is forbid to clear birth year
   */
  protected static $_is_forbid_to_clear_birth_year = FALSE;

  /**
   * Process pre hook
   *
   * @param $op
   * @param $objectName
   * @param $id
   * @param $params
   */
  public static function process_pre($op, $objectName, $id, &$params) {
    if ($objectName == 'Individual') {
      if (!array_key_exists('birth_date', $params) && (!empty($params['id']) || !empty($params['contact_id']))) {
        self::$_is_forbid_to_clear_birth_year = TRUE;
      }
    }
  }

  /**
   * process POST hook
   */
  public static function process_post($op, $objectName, $objectId, &$objectRef) {
    // fills the custom field with the correct birth year if someone updates CiviCRM's Birth Date field

    if ($objectRef instanceof CRM_Contact_DAO_Contact) {
      if (self::$_is_forbid_to_clear_birth_year) {
        return;
      }

      if (!empty($objectRef->birth_date) && $objectRef->birth_date != 'null') {
        // Contact Birth date has a value
        try {
          // Contact birth date to (long) year
          $contactBirthYear = (new DateTime($objectRef->birth_date))->format('Y');
        } catch (Exception $e) {
          return;
        }
      }
      else {
        $birthDate = civicrm_api3('Contact', 'getvalue', [
          'return' => 'birth_date',
          'id' => $objectRef->id,
        ]);

        if (!empty($birthDate)) {
          $birthYearField = self::getCustomField();
          $customValues = civicrm_api3('CustomValue', 'get', array(
            'entity_id' => $objectRef->id,
            'return.custom_'.$birthYearField['id'] => 1,
          ));

          if (empty($customValues['values'][$birthYearField['id']][0])) {
            return;
          }

          try {
            $contactBirthYear = (new DateTime($objectRef->birth_date))->format('Y');
          } catch (Exception $e) {
            return;
          }

          if ($contactBirthYear == $customValues['values'][$birthYearField['id']][0]) {
            return;
          }

          self::$_is_forbid_to_clear_birth_year = TRUE;

          //Delete birth_date
          $result = civicrm_api3('Contact', 'create', array(
            'id' => $objectRef->id,
            'birth_date' => '',
          ));

          self::$_is_forbid_to_clear_birth_year = FALSE;

          return;
        }
      }

      if (empty($contactBirthYear)) {
        return;
      }

      $birthYearField = self::getCustomField();
      // Update birth year custom field with new value
      $customValues = civicrm_api3('CustomValue', 'create', array(
        'entity_id' => $objectRef->id,
        "custom_{$birthYearField['id']}" => $contactBirthYear,
      ));

    }
  }

  /**
   * process CUSTOM hook
   */
  public static function process_custom( $op, $groupID, $entityID, &$params ) {
    $className = CRM_Utils_Request::retrieve('class_name', 'String');

    if ($className != 'CRM_Contact_Form_Inline_CustomData') {
      return;
    }

    $birthYearField = self::getCustomField();
    $groupID = CRM_Utils_Request::retrieve('groupID', 'Positive');

    if ($birthYearField['custom_group_id'] != $groupID) {
      return;
    }

    // deletes the values CiviCRM's Birth Date field if someone updates the custom field with a year that is contradictory to the birth date

    foreach ($params as $entity) {
      if (!empty($entity['entity_table']) && $entity['entity_table'] == 'civicrm_contact') {
        if (($birthYearField['column_name'] == $entity['column_name'])
          && ($birthYearField['custom_group_id'] == $entity['custom_group_id'])
        ) {
          // birth_year field was written
          // Get value of birth_year field
          $customValues = civicrm_api3('CustomValue', 'get', array(
            'entity_id' => $entity['entity_id'],
            'return.custom_'.$birthYearField['id'] => 1,
          ));
          $birthYear = $customValues['values'][$birthYearField['id']][0];

          // Get contact ID birth date field ($params['entity_id'])
          try {
            $contactBirthDate = civicrm_api3('Contact', 'getsingle', array(
              'return' => "birth_date",
              'id' => $entity['entity_id'],
            ));
          }
          catch (Exception $e) {
            //getsingle throws exception if not found
            return;
          }
          // Contact birth date to year
          if (!empty($contactBirthDate['birth_date'])) {
            try {
              $contactBirthYear = new DateTime($contactBirthDate['birth_date']);
            }
            catch (Exception $e) {
              return;
            }
            // Is birth date = birth year? (Match only long format)
            if ($contactBirthYear->format('Y') != $birthYear) {
              self::$_is_forbid_to_clear_birth_year = TRUE;

              //Delete birth_date
              $result = civicrm_api3('Contact', 'create', array(
                'id' => $entity['entity_id'],
                'birth_date' => '',
              ));

              self::$_is_forbid_to_clear_birth_year = FALSE;
            }
          }
        }
      }
    }
  }


  /**
   * process CUSTOM hook
   */
  public static function process_buildForm($formName, &$form) {
    if ($formName == 'CRM_Contact_Form_Inline_CustomData') {
      $birthyear_field = self::getCustomField();
      if ($birthyear_field) {
        $script = file_get_contents(__DIR__ . '/../../../js/extended_demographics_edit.js');
        $script = str_replace('BIRTH_YEAR_FIELD', $birthyear_field['id'], $script);
        CRM_Core_Region::instance('page-footer')->add(array(
          'script' => $script,
          ));
      }
    }
  }


  /**
   * Get the birthyear field (cached)
   *
   * copied from uk.co.mjwconsulting.birthyear
   */
  public static function getCustomField() {
    if (self::$_birthyear_custom_field === NULL) {
      // load custom field data
      self::$_birthyear_custom_field = civicrm_api3('CustomField', 'getsingle', array('name' => "birth_year"));
    }
    return self::$_birthyear_custom_field;
  }

  /**
   * Process validateForm hook
   *
   * @param $formName
   * @param $fields
   * @param $files
   * @param $form
   * @param $errors
   */
  public static function process_validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if ($formName == 'CRM_Contact_Form_Contact') {
      $birthYearField = self::getCustomField();
      $birthYearElementName = $form->_groupTree[$birthYearField['custom_group_id']]['fields'][$birthYearField['id']]['element_name'];

      if (empty($fields['birth_date']) || empty($fields[$birthYearElementName])) {
        return;
      }

      try {
        $contactBirthYear = (new DateTime($fields['birth_date']))->format('Y');
      } catch (Exception $e) {
        return;
      }

      if ($contactBirthYear != $fields[$birthYearElementName]) {
        $errors[$birthYearElementName] = ts('The Year of Birth should be the same as Birth Date');
        $errors['birth_date'] = ts('The Birth Date should be the same as Year of Birth');
      }
    }
  }

}
