<?php

class CRM_Uimods_Merge_MergeContact {

  private static $instance = null;
  private $mergeInformation = [];

  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new static();
    }

    return self::$instance;
  }

  public function __clone() {
    throw new Exception('__clone is not allowed.');
  }

  public function __wakeup() {
    throw new Exception('__wakeup is not allowed.');
  }

  /**
   * @param $mergeInformation
   * @return void
   */
  public function setMergeInformation($mergeInformation) {
    $this->mergeInformation = $mergeInformation;
  }

  /**
   * Fixes location type for phones after marge
   */
  public function postMergeFixPhones() {
    if (empty($this->mergeInformation)) {
      return;
    }

    $supportLocationTypeId = $this->getLocationTypeId('support');
    if (empty($supportLocationTypeId)) {
      return;
    }

    $updateDataItems = [];
    $beforeMergePhones = $this->getBeforeMergeItems('phone');
    $currentSupportPhones = civicrm_api3('Phone', 'get', [
      'contact_id' => $this->mergeInformation['main_details']['contact_id'],
      'location_type_id' => $supportLocationTypeId
    ]);

    foreach ($currentSupportPhones['values'] as $currentPhone) {
      foreach ($beforeMergePhones as $phoneBefore) {
        if ($currentPhone['phone'] == $phoneBefore['phone'] && $phoneBefore['location_type_id'] != $supportLocationTypeId) {
          $updateDataItems[] = [
            'phone_id' => $currentPhone['id'],
            'location_type_id' => $phoneBefore['location_type_id'],
          ];
        }
      }
    }

    if (!empty($updateDataItems)) {
      $ids = [];
      foreach ($updateDataItems as $phoneItem) {
        civicrm_api3('Phone', 'create', [
          'id' => $phoneItem['phone_id'],
          'location_type_id' => $phoneItem['location_type_id'],
        ]);
        $ids[] = $phoneItem['phone_id'];
      }

      CRM_Core_Session::setStatus(
        'Fixed location type in - ' . count($updateDataItems) . ' phones. Ids: ' . implode(',', $ids),
        ts('Post merge phone fixes'),
        'success'
      );
    }
  }

  /**
   * Fixes location type for emails after marge
   */
  public function postMergeFixEmails() {
    if (empty($this->mergeInformation)) {
      return;
    }

    $supportLocationTypeId = $this->getLocationTypeId('support');
    if (empty($supportLocationTypeId)) {
      return;
    }

    $updateDataItems = [];
    $beforeMergeEmails = $this->getBeforeMergeItems('email');
    $currentSupportEmails = civicrm_api3('Email', 'get', [
      'contact_id' => $this->mergeInformation['main_details']['contact_id'],
      'location_type_id' => $supportLocationTypeId
    ]);

    foreach ($currentSupportEmails['values'] as $currentEmail) {
      foreach ($beforeMergeEmails as $emailBefore) {
        if ($currentEmail['email'] == $emailBefore['email'] && $emailBefore['location_type_id'] != $supportLocationTypeId) {
          $updateDataItems[] = [
            'email_id' => $currentEmail['id'],
            'location_type_id' => $emailBefore['location_type_id'],
          ];
        }
      }
    }

    if (!empty($updateDataItems)) {
      $ids = [];
      foreach ($updateDataItems as $emailItem) {
        civicrm_api3('Email', 'create', [
          'id' => $emailItem['email_id'],
          'location_type_id' => $emailItem['location_type_id'],
        ]);
        $ids[] = $emailItem['email_id'];
      }

      CRM_Core_Session::setStatus(
        'Fixed location type in - ' . count($updateDataItems) . ' emails. Ids: ' . implode(',', $ids),
        ts('Post merge email fixes'),
        'success'
      );
    }
  }

  /**
   * @param $locationTypeName
   * @return false|string
   */
  private function getLocationTypeId($locationTypeName) {
    if (empty($locationTypeName)) {
      return false;
    }

    try {
      $locationType = civicrm_api3('LocationType', 'getsingle', [
        'name' => $locationTypeName,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      return false;
    }

    return $locationType['id'];
  }

  /**
   * Get items which exists before merge by merge info
   *
   * @param $entityName
   * @return array
   */
  private function getBeforeMergeItems($entityName): array {
    $beforeMergeItems = [];
    foreach ($this->mergeInformation['main_details']['location_blocks'][$entityName] as $beforeMergeItem) {
      $beforeMergeItems[] = $beforeMergeItem;
    }
    foreach ($this->mergeInformation['other_details']['location_blocks'][$entityName] as $beforeMergeItem) {
      $beforeMergeItems[] = $beforeMergeItem;
    }

    return $beforeMergeItems;
  }

}
