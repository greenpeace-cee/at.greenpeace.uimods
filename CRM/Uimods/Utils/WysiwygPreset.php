<?php

class CRM_Uimods_Utils_WysiwygPreset {

  public static function install() {
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

}
