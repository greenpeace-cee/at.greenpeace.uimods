<?php
use CRM_Uimods_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Uimods_Upgrader extends CRM_Extension_Upgrader_Base {

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

  public function upgrade_0164() {
    $this->ctx->log->info('Applying update 0164. Install civicrm_uimods_template table.');

    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `civicrm_uimods_template` (
        `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ApiLog ID',
        `scope_name` varchar(60) NOT NULL COMMENT 'Scope name of template',
        `target_value` varchar(100) NOT NULL COMMENT 'Target value',
        `field_name` varchar(100) NOT NULL COMMENT 'Field name',
        `field_value` longtext DEFAULT NULL COMMENT 'Field value',
        `field_type` varchar(30) NOT NULL COMMENT 'Field type',
        `is_field_hidden` tinyint NOT NULL COMMENT 'Is field hidden?',
        `contact_id` int unsigned COMMENT 'FK to Contact',
        `updated_at` datetime NOT NULL DEFAULT NOW() COMMENT 'Updated at date',
        `created_at` datetime NOT NULL DEFAULT NOW() COMMENT 'Created at date',
        PRIMARY KEY (`id`),
        CONSTRAINT FK_civicrm_uimods_template_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
      )
      ENGINE=InnoDB;
    ");

    $logging = new CRM_Logging_Schema();
    $logging->fixSchemaDifferences();

    return TRUE;
  }

}
