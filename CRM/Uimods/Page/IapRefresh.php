<?php
use CRM_Uimods_ExtensionUtil as E;

class CRM_Uimods_Page_IapRefresh extends CRM_Core_Page {

  public function run() {
    Civi::log()->warning('User reached IAP refresh page. This should not happen.');

    parent::run();
  }

}
