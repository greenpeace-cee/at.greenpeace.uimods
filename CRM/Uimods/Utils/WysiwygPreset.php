<?php

class CRM_Uimods_Utils_WysiwygPreset {

  /**
   * @return void
   * @throws Exception
   */
  public static function install() {
    self::installJsConfig();
    self::installOptionValue();
  }

  /**
   * @return void
   * @throws Exception
   */
  private static function installJsConfig() {
    $extensionDirectory = CRM_Uimods_Utils_Extension::getExtensionDirectory();
    $template = $extensionDirectory . 'resources/crm-ckeditor-uimods.js';
    if (!file_exists($template)) {
      throw new Exception('Uimods: install Wysiwyg Preset: cannot find crm-ckeditor-uimods.js');
    }

    $configFilePath = NULL;
    if (class_exists('CRM_Admin_Form_CKEditorConfig')) {
      $configFilePath = CRM_Admin_Form_CKEditorConfig::CONFIG_FILEPATH;
    } elseif (class_exists('CRM_Ckeditor4_Form_CKEditorConfig')) {
      $configFilePath = CRM_Ckeditor4_Form_CKEditorConfig::CONFIG_FILEPATH;
    } else {
      throw new Exception('Uimods: requires the ckeditor4 extension starting with CiviCRM 5.40');
    }

    $file = Civi::paths()->getPath($configFilePath . 'uimods' . '.js');
    file_put_contents($file, file_get_contents($template));
  }

  /**
   * @return void
   */
  private static function installOptionValue() {
    $optionGroupName = 'wysiwyg_presets';
    $optionValueName = 'uimods';

    if (CRM_Uimods_Utils_OptionValue::isExist($optionGroupName, $optionValueName)) {
      return;
    }

    try {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => $optionGroupName,
        'name' => $optionValueName,
        'label' => 'Uimods',
      ]);
    } catch (\CiviCRM_API3_Exception $e) {
      throw new Exception('Uimods: cannot create OptionValue where name is ' . $optionValueName . ', and option_group_id is: ' . $optionGroupName);
    }
  }

}
