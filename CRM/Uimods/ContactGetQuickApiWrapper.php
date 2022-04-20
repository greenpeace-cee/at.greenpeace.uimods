<?php

class CRM_Uimods_ContactGetQuickApiWrapper implements API_Wrapper
{
  /**
   * Method to update request (required from abstract class)
   *
   * @param array $apiRequest
   * @return array $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * @param array $apiRequest
   * @param array $result
   * @return array $result
   */
  public function toApiOutput($apiRequest, $result) {

    if ($result['count'] == 0 && isset($apiRequest['params']['name']) && isset($apiRequest['params']['field_name']) &&
      $apiRequest['params']['field_name'] == 'contact_id' && !isset($apiRequest['params']['avoid_endless_loop'])
      )
    {
      $resultIdentity = civicrm_api3('Contact', 'findbyidentity', [
        'identifier' => $apiRequest['params']['name'],
        'identifier_type' => "internal",
      ]);

      if ($resultIdentity['count'] > 0 && isset(reset($resultIdentity['values'])['id'])) {
        $resultGetQuick = civicrm_api3('Contact', 'getquick', [
          'name' => (int) reset($resultIdentity['values'])['id'],
          'field_name' => "contact_id",
          'avoid_endless_loop' => 1
        ]);
        if ($resultGetQuick['count'] > 0) {
          return $resultGetQuick;
        }
      }
    }
    return $result;
  }
}
