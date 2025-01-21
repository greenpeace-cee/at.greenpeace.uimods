<?php
/*-------------------------------------------------------+
| Greenpeace UI Modifications                            |
| Copyright (C) 2017 SYSTOPIA                            |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

/*
* Settings metadata file
*/
return [
  'at_greenpeace_uimods_config' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_config',
    'type' => 'Array',
    'default' => array(),
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Stored information on custom fields',
  ],
  'at_greenpeace_uimods_preferred_language' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_preferred_language',
    'type' => 'string',
    'default' => 'de_DE',
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Default preferred language for new contacts',
  ],
  'at_greenpeace_uimods_token_sql_task_id' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_token_sql_task_id',
    'type' => 'integer',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'SQL Task called to generate contact tokens',
  ],
  'at_greenpeace_uimods_sms_default_location_type_id' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_sms_default_location_type_id',
    'type' => 'integer',
    'default' => NULL,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Default location type for new phone numbers generated from inbound SMS',
  ],
  'at_greenpeace_uimods_sms_discard_unknown_sender' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_sms_discard_unknown_sender',
    'type' => 'Boolean',
    'default' => FALSE,
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Discard SMS from unknown sender?',
  ],
  'at_greenpeace_uimods_activity_assignees_filter_group_name' => [
    'group_name' => 'GP UIMods',
    'group' => 'at_greenpeace_uimods',
    'name' => 'at_greenpeace_uimods_activity_assignees_filter_group_name',
    'type' => 'string',
    'default' => '',
    'add' => '4.6',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Group name, which uses for filter at "activity_assignees" field.',
  ],
 ];
