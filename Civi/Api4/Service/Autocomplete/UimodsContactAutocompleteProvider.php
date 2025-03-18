<?php

namespace Civi\Api4\Service\Autocomplete;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Setting;
use Civi\Core\Event\GenericHookEvent;
use Civi\Core\HookInterface;
use Civi\Core\Service\AutoService;

/**
 * @service
 * @internal
 */
class UimodsContactAutocompleteProvider extends AutoService implements HookInterface {

  /**
   * Using a different alias for email_primary.email and phone_primary.phone
   * doesn't work reliably, but "overwriting" it like this seems to work ...
   */
  const FIXED_SELECT_FIELDS = [
    'id',
    'sort_name',
    'phone_primary.phone_numeric',
    'phone_primary.phone',
    'email_primary.email',
    'GROUP_FIRST(email_primary.email ORDER BY email_primary.is_primary DESC) AS email_primary.email',
    'GROUP_FIRST(phone_primary.phone ORDER BY phone_primary.is_primary DESC) AS phone_primary.phone',
    'address_primary.street_address',
    'address_primary.city',
    'address_primary.country_id:abbr',
    'address_primary.postal_code',
    'first_name',
    'last_name',
  ];

  /**
   * Set filters for the menubar quicksearch.
   *
   * @param \Civi\API\Event\PrepareEvent $event
   */
  public static function on_civi_api_prepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if (is_object($apiRequest) &&
      is_a($apiRequest, 'Civi\Api4\Generic\AutocompleteAction') &&
      $apiRequest->getFormName() === 'crmMenubar' &&
      $apiRequest->getFieldName() === 'crm-qsearch-input'
    ) {
      if (count($apiRequest->getFilters()) == 0 && \Civi::settings()->get('includeEmailInName')) {
        // we're searching by sort_name + email. this defaults to email LIKE input OR sort_name LIKE input
        // by default, which performs badly on larger databases.
        // instead, we check if we're dealing with an email by looking for @
        // and search exclusively by sort_name OR by email depending on the presence of @
        if (strpos($apiRequest->getInput(), '@') !== FALSE) {
          $apiRequest->addFilter('email_primary.email', $apiRequest->getInput());
        }
        else {
          $apiRequest->addFilter('sort_name', $apiRequest->getInput());
        }
      }

      if (function_exists('identitytracker_civicrm_install') &&
        !empty($apiRequest->getFilters()['id']) &&
        \CRM_Utils_Rule::positiveInteger($apiRequest->getFilters()['id'])
      ) {
        // we're searching by contact ID. The ID could belong to a contact
        // deleted after merging, in which case we want to rewrite the search
        // to use the new ID instead
        $resultIdentity = \civicrm_api3('Contact', 'findbyidentity', [
          'identifier' => $apiRequest->getFilters()['id'],
          'identifier_type' => 'internal',
        ]);

        if ($resultIdentity['count'] > 0 && !empty(reset($resultIdentity['values'])['id'])) {
          $filters = $apiRequest->getFilters();
          $filters['id'] = reset($resultIdentity['values'])['id'];
          $apiRequest->setFilters($filters);
        }
      }

      if (!empty($apiRequest->getFilters()['phone_primary.phone_numeric'])) {
        $filters = $apiRequest->getFilters();
        // strip +, (, ) and whitespace. we want to be less thorough than phone_numeric
        // so we don't trigger phone searches for name/email/etc. inputs
        $strippedPhone = preg_replace('/[+()\s]/', '', $filters['phone_primary.phone_numeric']);
        // make sure stripped phone is not completely empty; otherwise this just returns all contacts and looks silly
        if (!empty($strippedPhone)) {
          $filters['phone_primary.phone_numeric'] = $strippedPhone;
          $apiRequest->setFilters($filters);
        }
      }
    }
  }

  public static function on_civi_search_autocompleteDefault(GenericHookEvent $event) {
    if ($event->formName === 'crmMenubar' && $event->fieldName === 'crm-qsearch-input') {
      $event->savedSearch['api_params']['join'][] = [
        'Phone AS phone_primary', 'LEFT', ['id', '=', 'phone_primary.contact_id'],
      ];
      $event->savedSearch['api_params']['join'][] = [
        'Email AS email_primary', 'LEFT', ['id', '=', 'email_primary.contact_id'],
      ];
      // TODO: using GROUP BY id and the rewrite hack in on_civi_search_defaultDisplay
      //   force us to use explicit select fields here. try to solve in a less hacky way
      $event->savedSearch['api_params']['select'] = self::FIXED_SELECT_FIELDS;
      $event->savedSearch['api_params']['groupBy'] = [
        'id',
      ];
    }
  }

  public static function on_civi_search_defaultDisplay(GenericHookEvent $e) {
    if (($e->context['formName'] ?? NULL) === 'crmMenubar' && ($e->context['fieldName'] ?? NULL) === 'crm-qsearch-input') {
      $mainColumn = $e->display['settings']['columns'][0];
      $mainColumn['rewrite'] = '[sort_name]{if !empty("[email_primary.email]")} 📧 [email_primary.email]{/if}{if !empty("[birth_date]")} 🎂 [birth_date]{/if}{if !empty("[phone_primary.phone]")}  📞 [phone_primary.phone]{/if}{if !empty("[address_primary.street_address]") || !empty("[address_primary.postal_code]") || !empty("[address_primary.city]")} 🏠{/if}{if !empty("[address_primary.street_address]")} [address_primary.street_address],{/if}{if !empty("[address_primary.postal_code]")} {if !empty("[address_primary.country_id:abbr]")}[address_primary.country_id:abbr]-{/if}[address_primary.postal_code]{/if}{if !empty("[address_primary.city]")} [address_primary.city]{/if}';
      if (!in_array($mainColumn['key'], self::FIXED_SELECT_FIELDS)) {
        $mainColumn['rewrite'] .= ' :: [' . $mainColumn['key'] . ']';
      }
      $e->display['settings']['columns'] = [$mainColumn];
    }
  }

}
