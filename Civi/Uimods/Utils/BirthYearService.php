<?php

namespace Civi\Uimods\Utils;

use CRM_Core_DAO;
use Civi\Api4\Contact;
use Civi\Api4\CustomField;

class BirthYearService {

  protected static bool $isAllowToUpdateBirthYear = true;

  public static function forbidToUpdateBirthYear() {
    BirthYearService::$isAllowToUpdateBirthYear = false;
  }

  public static function allowToUpdateBirthYear() {
    BirthYearService::$isAllowToUpdateBirthYear = true;
  }

  public static function isAllowToUpdateBirthYear(): bool {
    return BirthYearService::$isAllowToUpdateBirthYear;
  }

  public static function getBirthYearCustomField(): array {
    $customFields = CustomField::get(false)
      ->addSelect('custom_group_id', 'custom_group_id.table_name', 'name', 'column_name', 'custom_group_id.name')
      ->addWhere('custom_group_id:name', '=', 'additional_demographics')
      ->addWhere('name', '=', 'birth_year')
      ->setLimit(1)
      ->execute();

    foreach ($customFields as $customField) {
      return $customField;
    }

    return [];
  }

  public static function getBirthYearFieldValue($contactId) {
    $contacts = Contact::get(false)
      ->addSelect('id', 'additional_demographics.birth_year')
      ->addWhere('id', '=', $contactId)
      ->setLimit(1)
      ->execute();

    $contact = $contacts->first();

    if (!empty($contact)) {
      return $contact['additional_demographics.birth_year'];
    }

    return null;
  }

  public static function getBirthDateFieldValue($contactId) {
    $contacts = Contact::get(false)
      ->addSelect('id', 'birth_date')
      ->addWhere('id', '=', $contactId)
      ->setLimit(1)
      ->execute();

    $contact = $contacts->first();

    if (!empty($contact)) {
      return $contact['birth_date'];
    }

    return null;
  }

  public static function getSetBirthYearSQL($contactId, $birthYear): string {
    $customField = BirthYearService::getBirthYearCustomField();
    $tableName = $customField['custom_group_id.table_name'];

    return BirthYearService::getSetCustomFieldSQL($contactId, $tableName, $customField['column_name'], $birthYear, 'String');
  }

  public static function getSetBirthDateSQL($contactId, $birthDate): string {
    return CRM_Core_DAO::composeQuery(
      'UPDATE civicrm_contact SET birth_date = %1 WHERE id = %2',
      [
        1 => [$birthDate, 'String'],
        2 => [$contactId, 'Integer']
      ]
    );
  }

  public static function isCustomFieldEntityExist($tableName, $entityId): string {
    $customFieldEntity = CRM_Core_DAO::executeQuery(
      'SELECT * FROM ' . $tableName . ' WHERE entity_id = %1',
      [
        1 => [$entityId, 'Integer']
      ]
    );

    while ($customFieldEntity->fetch()) {
      return true;
    }

    return false;
  }

  public static function getSetCustomFieldSQL($entityId, $tableName, $columnName, $value, $valueType): string {
    if (BirthYearService::isCustomFieldEntityExist($tableName, $entityId)) {
      return CRM_Core_DAO::composeQuery(
        'UPDATE ' . $tableName . ' SET ' . $columnName . ' = %2 WHERE entity_id = %1',
        [
          1 => [$entityId, 'Integer'],
          2 => [$value, $valueType]
        ]
      );
    }

    return CRM_Core_DAO::composeQuery(
      'INSERT INTO ' . $tableName . ' (entity_id, ' . $columnName . ') VALUES(%1, %2)',
      [
        1 => [$entityId, 'Integer'],
        2 => [$value, $valueType]
      ]
    );
  }

  public static function clearBirthDate($contactId): void {
    civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      'birth_date' => '',
    ]);
  }

  public static function saveBirthYear($contactId, $birthYear): void {
    $birthYearField = BirthYearService::getBirthYearCustomField();

    civicrm_api3('CustomValue', 'create', [
      'entity_id' => $contactId,
      "custom_{$birthYearField['id']}" => $birthYear,
    ]);
  }

}
