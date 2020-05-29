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
 
/**
 * Class to render CiviBanking accounts
 */
class CRM_Uimods_Tools_BankAccount {

  /**
   * @var array
   */
  protected static $bank_account_reference_options = [];

  /**
   * passing the build_form hook
   */
  public static function renderForm($formName, &$form) {
    if (  $formName == 'CRM_Contribute_Form_ContributionView'
       || $formName == 'CRM_Activity_Form_Activity') {
      $viewCustomData = $form->get_template_vars('viewCustomData');
      // $contact_id = self::getContactID($form);
      $modified = FALSE;

      $bank_account_fields = CRM_Uimods_Config::getSingleton()->getAccountCustomFields();
      foreach ($bank_account_fields as $custom_group_id => $custom_field_ids) {
        if (!isset($viewCustomData[$custom_group_id])) continue;
        foreach ($viewCustomData[$custom_group_id] as &$groupCustomData) {
          foreach ($custom_field_ids as $custom_field_id) {
            if (isset($groupCustomData['fields'][$custom_field_id]['field_value'])) {
              $groupCustomData['fields'][$custom_field_id]['field_value'] =
                self::renderBankAccount($groupCustomData['fields'][$custom_field_id]['field_value']);
              $modified = TRUE;
            }
          }
        }
      }

      if ($modified) {
        // write back if changed
        $form->assign('viewCustomData', $viewCustomData);
      }
    }

    if ($formName == 'CRM_Custom_Form_CustomDataByType') {
      $type = CRM_Utils_Request::retrieveValue('type', 'String');
      if ($type == 'Contribution' && $form->getAction() == CRM_Core_Action::UPDATE) {
        $to_ba_name = CRM_Uimods_Config::getOutgoingBAField() . '_' . $form->getVar('_entityId');
        $from_ba_name = CRM_Uimods_Config::getIncomingBAField() . '_' . $form->getVar('_entityId');

        if ($form->elementExists($to_ba_name)) {
          $label = $form->getElement($to_ba_name)->getLabel();
          $form->removeElement($to_ba_name);
          $options = ['' => ts('-- please select --')] + self::getBankAccountReferenceOptions()['all_domain_ibans'];
          $form->add('select', $to_ba_name, $label, $options, FALSE, ['class' => 'crm-select2']);
        }

        if ($form->elementExists($from_ba_name)) {
          $label = $form->getElement($from_ba_name)->getLabel();
          $form->removeElement($from_ba_name);
          $options = ['' => ts('-- please select --')] + self::getBankAccountReferenceOptions()['all_ibans'];
          $form->add('select', $from_ba_name, $label, $options, FALSE, ['class' => 'crm-select2',]);
        }
      }
    }
  }

  /**
   * tries to extract the contact ID from the given form
   */
  public static function getContactID(&$form) {
    $contact_id = $form->get_template_vars('contact_id');
    if (empty($contact_id)) {
      // try another type...
      $contact_id = $form->get_template_vars('contactId');
    }

    return $contact_id;
  }


  /**
   * Will render a bank account for the UI
   * 
   * @return HTML snippet
   */
  public static function renderBankAccount($ba_id) {
    $ba_id = (int) $ba_id;
    $reference = self::getPrimaryBankAccountReference($ba_id);
    
    if (empty($ba_id)) {
      return "(not set)";
    } elseif (empty($reference)) {
      return "invalid";
    } else {
      $bank_accounts = civicrm_api3('BankingAccount', 'get', array('id' => $ba_id, 'return' => 'contact_id'));
      $ba = reset($bank_accounts['values']);
      if (empty($ba['contact_id'])) {
        return $reference;
      } else {
        $link = CRM_Utils_System::url('civicrm/contact/view', "reset=1&amp;cid={$ba['contact_id']}&amp;selectedChild=bank_accounts");
        return "<a href=\"{$link}\">{$reference}</a>";        
      }
    }
  }

  /**
   * will return the primary reference for the give bank account,
   * determined by importance: IBAN > NBAN_AT > others
   */
  public static function getPrimaryBankAccountReference($ba_id) {
    $ba_id = (int) $ba_id;
    $NBAN_AT_references = array();
    $OTHER_references = array();
    $references = CRM_Core_DAO::executeQuery("SELECT reference, civicrm_option_value.name AS reference_type FROM civicrm_bank_account_reference LEFT JOIN civicrm_option_value ON reference_type_id=civicrm_option_value.id WHERE ba_id = {$ba_id};");
    while ($references->fetch()) {
      switch ($references->reference_type) {
        case 'IBAN':
          return $references->reference;
        
        case 'NBAN_AT':
          $NBAN_AT_references[] = $references->reference;
          break;

        default:
          $OTHER_references[] = $references->reference;
          break;
      }

      if (!empty($NBAN_AT_references)) {
        return $NBAN_AT_references[0];
      } elseif (!empty($OTHER_references)) {
        return $OTHER_references[0];
      } else {
        return NULL;
      }
    }
  }

  /**
   * Get options of all IBANs and IBANs of default domain.
   *
   * @return array
   */
  public static function getBankAccountReferenceOptions() {
    if (empty(self::$bank_account_reference_options)) {
      $default_contact_id = (int) civicrm_api3('Domain', 'getvalue', [
        'return' => 'contact_id',
        'id' => CRM_Core_Config::domainID(),
      ]);
      $bar = civicrm_api3('BankingAccountReference', 'get', [
        'api.BankingAccount.getcount' => [
          'id' => '$value.ba_id',
          'contact_id' => $default_contact_id,
        ],
      ]);
      $options = [
        'all_ibans' => [],
        'all_domain_ibans' => []
      ];

      foreach ($bar['values'] as $value) {
        $options['all_ibans'][$value['ba_id']] = $value['reference'];
        if ($value['api.BankingAccount.getcount'] > 0) {
          $options['all_domain_ibans'][$value['ba_id']] = $value['reference'];
        }
      }
      self::$bank_account_reference_options = $options;
    }

    return self::$bank_account_reference_options;
  }

}
