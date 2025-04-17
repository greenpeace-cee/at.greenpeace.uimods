<?php

use CRM_Uimods_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_additional_demographics',
    'entity' => 'CustomGroup',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'additional_demographics',
        'title' => E::ts('Extended Demographics'),
        'style' => 'Inline',
        'help_pre' => '',
        'help_post' => '',
        'collapse_adv_display' => TRUE,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_additional_demographics_CustomField_birth_year',
    'entity' => 'CustomField',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'additional_demographics',
        'name' => 'birth_year',
        'label' => E::ts('Year of Birth'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'is_searchable' => TRUE,
        'is_search_range' => TRUE,
        'column_name' => 'birth_year',
      ],
      'match' => [
        'custom_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions',
    'entity' => 'OptionGroup',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'dm_restrictions',
        'title' => E::ts('DM Restrictions'),
        'is_reserved' => FALSE,
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_No_Restrictions',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('No Restrictions'),
        'value' => '12',
        'name' => 'No Restrictions',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_Exceptions_Only',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('Exceptions Only'),
        'value' => '0',
        'name' => 'Exceptions Only',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_1_Mailing',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('1 Mailing'),
        'value' => '1',
        'name' => '1 Mailing',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_2_Mailings',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('2 Mailings'),
        'value' => '2',
        'name' => '2 Mailings',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_3_Mailings',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('3 Mailings'),
        'value' => '3',
        'name' => '3 Mailings',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_dm_restrictions_OptionValue_4_Mailings',
    'entity' => 'OptionValue',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'dm_restrictions',
        'label' => E::ts('4 Mailings'),
        'value' => '4',
        'name' => '4 Mailings',
      ],
      'match' => [
        'option_group_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_additional_demographics_CustomField_dm_restrictions',
    'entity' => 'CustomField',
    'cleanup' => 'never',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'additional_demographics',
        'name' => 'dm_restrictions',
        'label' => E::ts('DM Restrictions'),
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'is_search_range' => TRUE,
        'column_name' => 'dm_restrictions',
        'option_group_id.name' => 'dm_restrictions',
      ],
      'match' => [
        'custom_group_id',
        'name',
      ],
    ],
  ],
];
