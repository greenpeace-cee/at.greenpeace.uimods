<?php

class CRM_Uimods_Utils_Civioffice {

  public static function getContactSettingsCreateSingleActivityType() {
    CRM_Uimods_Utils_Civioffice::isCiviofficeClassExist();

    return Civi::contactSettings()->get(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_TYPE);
  }
  public static function getContactSettingsCreateSingleActivityAttachment() {
    CRM_Uimods_Utils_Civioffice::isCiviofficeClassExist();

    return Civi::contactSettings()->get(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_ATTACHMENT);
  }

  public static function setContactSettingsCreateSingleActivityType($value) {
    CRM_Uimods_Utils_Civioffice::isCiviofficeClassExist();

    try {
      Civi::contactSettings()->set(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_TYPE, $value);
    } catch (CRM_Core_Exception $ex) {
      Civi::log()->warning("uimods: Couldn't save defaults: " . $ex->getMessage());
    }
  }

  public static function setContactSettingsCreateSingleActivityAttachment($value) {
    CRM_Uimods_Utils_Civioffice::isCiviofficeClassExist();

    try {
      Civi::contactSettings()->set(CRM_Civioffice_Form_DocumentFromSingleContact::UNOCONV_CREATE_SINGLE_ACTIVIY_ATTACHMENT, $value);
    } catch (CRM_Core_Exception $ex) {
      Civi::log()->warning("uimods: Couldn't save defaults: " . $ex->getMessage());
    }
  }

  protected static function isCiviofficeClassExist() {
    if (!class_exists('CRM_Civioffice_Form_DocumentFromSingleContact')) {
      $message = "uimods: Couldn't getContactSettings: CRM_Civioffice_Form_DocumentFromSingleContact class doesn't exist.";
      Civi::log()->error($message);
      throw new CRM_Core_Exception($message);
    }
  }

  /**
   * @return array
   */
  public static function getMimetypes() {
    $config = CRM_Civioffice_Configuration::getConfig();
    $mimetypesList = [];

    foreach ($config->getDocumentRenderers(true) as $dr) {
      foreach ($dr->getType()->getSupportedOutputMimeTypes() as $mimeType) {
        $mimetypesList[$mimeType] = CRM_Civioffice_MimeType::mapMimeTypeToFileExtension($mimeType);
      }
    }

    return $mimetypesList;
  }

  /**
   * @return array
   */
  public static function getDocumentRendererList() {
    $config = CRM_Civioffice_Configuration::getConfig();
    $documentRendererList = [];

    foreach ($config->getDocumentRenderers(true) as $dr) {
      $documentRendererList[$dr->getURI()] = $dr->getName();
    }

    return $documentRendererList;
  }

  /**
   * @return array
   */
  public static function getDocumentList() {
    $config = CRM_Civioffice_Configuration::getConfig();

    return $config->getDocuments(true);
  }

}
