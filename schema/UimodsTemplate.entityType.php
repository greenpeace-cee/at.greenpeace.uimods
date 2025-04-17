<?php
use CRM_Uimods_ExtensionUtil as E;

return [
  'name' => 'UimodsTemplate',
  'table' => 'civicrm_uimods_template',
  'class' => 'CRM_Uimods_DAO_UimodsTemplate',
  'getInfo' => fn() => [
    'title' => E::ts('Uimods Template'),
    'title_plural' => E::ts('Uimods Templates'),
    'description' => E::ts('Saves data for uimods templates'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ApiLog ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'scope_name' => [
      'title' => E::ts('Scope Name of Template'),
      'sql_type' => 'varchar(60)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Scope name of template'),
    ],
    'target_value' => [
      'title' => E::ts('Target value'),
      'sql_type' => 'varchar(100)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Target value'),
    ],
    'field_name' => [
      'title' => E::ts('Field name'),
      'sql_type' => 'varchar(100)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Field name'),
    ],
    'field_value' => [
      'title' => E::ts('Field value'),
      'sql_type' => 'longtext',
      'input_type' => 'Text',
      'description' => E::ts('Field value'),
      'default' => NULL,
    ],
    'field_type' => [
      'title' => E::ts('Field Type'),
      'sql_type' => 'varchar(30)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Field type'),
    ],
    'is_field_hidden' => [
      'title' => E::ts('Is Field Hidden?'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Is field hidden?'),
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
    'updated_at' => [
      'title' => E::ts('Updated at'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('Updated at date'),
      'default' => 'NOW()',
    ],
    'created_at' => [
      'title' => E::ts('Created at'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('Created at date'),
      'default' => 'NOW()',
    ],
  ],
];
