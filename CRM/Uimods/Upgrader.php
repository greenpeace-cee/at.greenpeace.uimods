<?php
use CRM_Uimods_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Uimods_Upgrader extends CRM_Uimods_Upgrader_Base {

  /**
   * Runs after extension is installed
   */
  public function onPostInstall() {
    CRM_Uimods_Utils_WysiwygPreset::install();
  }

  public function upgrade_0162() {
    $this->ctx->log->info('Applying update 0162');
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_uimods_token` (
        `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique UimodsToken ID',
        `contact_id` int unsigned COMMENT 'FK to Contact',
        `tokens` longtext COMMENT 'Tokens (JSON)',
        PRIMARY KEY (`id`),
        UNIQUE INDEX `UI_contact_id`(contact_id),
        CONSTRAINT FK_civicrm_uimods_token_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
      )
      ENGINE=InnoDB;
    "
    );
    $logging = new CRM_Logging_Schema();
    $logging->fixSchemaDifferences();
    return TRUE;
  }

  public function upgrade_0163() {
    $this->ctx->log->info('Applying update 0163. Install wysiwyg presets.');
    CRM_Uimods_Utils_WysiwygPreset::install();

    return TRUE;
  }

}
