<?php

namespace Civi\Uimods;

use Civi\Token\Event\TokenValueEvent;
use Civi\Api4\UimodsToken;
class EvaluateTokens {

  /**
   * @param TokenValueEvent $e
   *
   * @return void
   * @throws \API_Exception
   * @throws \CiviCRM_API3_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public static function run(TokenValueEvent $e) {
    $tokens = $e->getTokenProcessor()->getMessageTokens();
    if (!empty($tokens['uimods']) && !empty(\Civi::settings()->get('at_greenpeace_uimods_token_sql_task_id'))) {
      foreach ($e->getRows() as $row) {
        if (!empty($row->context['contactId'])) {
          try {
            \civicrm_api3('Sqltask', 'execute', [
              'id' => \Civi::settings()->get('at_greenpeace_uimods_token_sql_task_id'),
              'input_val' => \json_encode(['contact_id' => $row->context['contactId']]),
              'log_to_file' => 1,
            ]);
            $uimodsTokens = UimodsToken::get(FALSE)
              ->addSelect('tokens')
              ->addWhere('contact_id', '=', $row->context['contactId'])
              ->execute()
              ->first();
            foreach ($uimodsTokens['tokens'] ?? [] as $key => $value) {
              $row->tokens('uimods', $key, $value);
            }
          } catch (Exception $e) {
            Civi::log()
              ->warning("[UIMods] Encountered error while calculating tokens for contact {$row->context['contactId']}: {$e->getMessage()}");
          }
        }
      }
    }
  }

}
