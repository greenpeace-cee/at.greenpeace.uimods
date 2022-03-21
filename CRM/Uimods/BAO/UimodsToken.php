<?php
use CRM_Uimods_ExtensionUtil as E;

class CRM_Uimods_BAO_UimodsToken extends CRM_Uimods_DAO_UimodsToken {

  /**
   * Create a new UimodsToken based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Uimods_DAO_UimodsToken|NULL
   *
  public static function create($params) {
    $className = 'CRM_Uimods_DAO_UimodsToken';
    $entityName = 'UimodsToken';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
