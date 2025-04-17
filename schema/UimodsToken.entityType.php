<?php
use CRM_Uimods_ExtensionUtil as E;

return [
  'name' => 'UimodsToken',
  'table' => 'civicrm_uimods_token',
  'class' => 'CRM_Uimods_DAO_UimodsToken',
  'getInfo' => fn() => [
    'title' => E::ts('Uimods Token'),
    'title_plural' => E::ts('Uimods Tokens'),
    'description' => E::ts(''),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_contact_id' => [
      'fields' => [
        'contact_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique UimodsToken ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'tokens' => [
      'title' => E::ts('Tokens'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
      'description' => E::ts('Tokens (JSON)'),
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
