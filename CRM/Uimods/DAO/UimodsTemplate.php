<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id
 * @property string $scope_name
 * @property string $target_value
 * @property string $field_name
 * @property string $field_value
 * @property string $field_type
 * @property bool|string $is_field_hidden
 * @property string $contact_id
 * @property string $updated_at
 * @property string $created_at
 */
class CRM_Uimods_DAO_UimodsTemplate extends CRM_Uimods_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_uimods_template';

}
