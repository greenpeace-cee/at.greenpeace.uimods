<?php

namespace Civi\Uimods;

use CiviCRM_API3_Exception;
use CRM_Core_Session;
use CRM_Uimods_Tools_BirthYear;
use DateTime;
use Exception;

class MergeContact {

  private static ?MergeContact $instance = null;

  private array $mergeInformation = [];

  private ?int $mainContactId = null;

  private ?int $secondaryContactId = null;

  public static function getInstance():MergeContact {
    if (is_null(self::$instance)) {
      self::$instance = new MergeContact();
    }

    return self::$instance;
  }

  public function __clone() {
    throw new Exception('__clone is not allowed.');
  }

  public function __wakeup() {
    throw new Exception('__wakeup is not allowed.');
  }

  public function setData(array $mergeInformation, int $mainContactId, int $secondaryContactId): void {
    $this->mergeInformation = $mergeInformation;
    $this->mainContactId = $mainContactId;
    $this->secondaryContactId = $secondaryContactId;
  }

  public function postMergeFixPhones(): void {
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
      'contact_id' => $this->mainContactId,
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

  public function postMergeFixBirth(): array {
    $sqlList = [];
    if (empty($this->mergeInformation)) {
      return $sqlList;
    }

    $mainContactBirthDate = CRM_Uimods_Tools_BirthYear::getBirthDateFieldValue($this->mainContactId);
    $mainContactBirthYear = CRM_Uimods_Tools_BirthYear::getBirthYearFieldValue($this->mainContactId);
    $secondaryContactBirthDate = CRM_Uimods_Tools_BirthYear::getBirthDateFieldValue($this->secondaryContactId);
    $secondaryContactBirthYear = CRM_Uimods_Tools_BirthYear::getBirthYearFieldValue($this->secondaryContactId);

    if (empty($mainContactBirthDate) && !empty($secondaryContactBirthDate)) {
      $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthDateSQL($this->mainContactId, $secondaryContactBirthDate);
    }

    if (empty($mainContactBirthYear) && !empty($secondaryContactBirthYear)) {
      $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthYearSQL($this->mainContactId, $secondaryContactBirthYear);
    }

    if (!empty($mainContactBirthDate) && !empty($secondaryContactBirthDate)) {
      $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthDateSQL($this->mainContactId, $mainContactBirthDate);
    }

    if (!empty($mainContactBirthYear) && !empty($secondaryContactBirthYear)) {
      $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthYearSQL($this->mainContactId, $mainContactBirthYear);
    }

    if (empty($mainContactBirthYear) && empty($secondaryContactBirthYear) && !empty($mainContactBirthDate)) {
      try {
        $birthYear = (new DateTime($mainContactBirthDate))->format('Y');
        $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthYearSQL($this->mainContactId, $birthYear);
      } catch (Exception $e) {}
    }

    if (empty($mainContactBirthYear) && empty($secondaryContactBirthYear) && !empty($secondaryContactBirthDate)) {
      try {
        $birthYear = (new DateTime($secondaryContactBirthDate))->format('Y');
        $sqlList[] = CRM_Uimods_Tools_BirthYear::getSetBirthYearSQL($this->mainContactId, $birthYear);
      } catch (Exception $e) {}
    }

    CRM_Core_Session::setStatus('Fixed year of birth in contact id: ' . $this->mainContactId, ts('Post merge'), 'success');

    return $sqlList;
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
      'contact_id' => $this->mainContactId,
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
