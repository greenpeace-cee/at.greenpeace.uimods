<?php
use CRM_Uimods_ExtensionUtil as E;

/**
 * GreenpeaceMessageTemplate.Send API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_greenpeace_message_template_send_spec(&$spec) {
  $spec['id'] = [
    'name'         => 'id',
    'title'        => 'MessageTemplate ID',
    'type'         => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];

  $spec['to_email'] = [
    'name'         => 'to_email',
    'title'        => 'To Emails (comma separated list)',
    'type'         => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  ];
}

/**
 * GreenpeaceMessageTemplate.Send API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_greenpeace_message_template_send($params) {

  $return_values = [
    'valid' => [],
    'invalid' => []
  ];

  try {
    $emails = explode(",", $params['to_email']);

    foreach ($emails as $email) {
      $email = trim($email);
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $return_values['invalid'][] = $email;
      } else {
        $return_values['valid'][] = $email;
      }
    }

    if (count($return_values['valid']) > 0) {
      $toEmail = '';
      $ccEmails = [];
      $emailNumber = 1;

      foreach ($return_values['valid'] as $validEmail) {
        if ($emailNumber === 1) {
          $toEmail = $validEmail;
        } else {
          $ccEmails[] = $validEmail;
        }
        $emailNumber++;
      }

      civicrm_api3('MessageTemplate', 'send', array_merge($params, [
        'cc' => implode(',', $ccEmails),
        'to_email' => $toEmail
      ]));
    }
  } catch (CiviCRM_API3_Exception $e) {
    throw new API_Exception('MessageTemplate send failed: ', $e->getMessage());
  }

  return civicrm_api3_create_success($return_values, $params, 'MessageTemplate', 'send');
}
