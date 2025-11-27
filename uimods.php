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

use Civi\Api4\UimodsToken;
use Civi\Uimods\Hooks\BuildForm\ImproveActivityAssigneesField;
use Civi\Uimods\MergeContact;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require_once 'uimods.civix.php';

/**
 * Implements hook_civicrm_pre()
 */
function uimods_civicrm_pre($op, $objectName, $id, &$params) {
  CRM_Uimods_Tools_BirthYear::processPreHook($op, $objectName, $id, $params);

  // GP-815: for newly created contacts:
  if ($op == 'create' && !$id && ($objectName == 'Individual' || $objectName == 'Organization')) {
    $preferredLanguage = civicrm_api3('Setting', 'GetValue', [
      'name' => 'at_greenpeace_uimods_preferred_language',
      'group' => 'GP UIMods'
    ]);
    if (empty($preferredLanguage)) {
      $default = civicrm_api3('Setting', 'getdefaults', [
        'name' => 'at_greenpeace_uimods_preferred_language',
        'group' => 'GP UIMods'
      ]);
      $preferredLanguage = reset($default['values'])['at_greenpeace_uimods_preferred_language'];
    }
    // ...set preferred language to configured language
    $params['preferred_language'] = $preferredLanguage;
  }
}

/**
 * Implements hook_civicrm_post()
 */
function uimods_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  CRM_Uimods_Tools_BirthYear::processPostHook($op, $objectName, $objectId, $objectRef);
}

/**
 * Implements hook_civicrm_custom
 */
function uimods_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  CRM_Uimods_Tools_BirthYear::processCustomHook($op, $groupID, $entityID, $params);
}

/**
 * Implements hook_civicrm_searchColumns
 */
function uimods_civicrm_searchColumns( $objectName, &$headers, &$rows, &$selector ) {
  if ($objectName == 'activity') {
    CRM_Uimods_Tools_SearchTableAdjustments::adjustActivityTable($objectName, $headers, $rows, $selector);
  } elseif ($objectName == 'contribution') {
    CRM_Uimods_Tools_SearchTableAdjustments::adjustContributionTable($objectName, $headers, $rows, $selector);
  } elseif ($objectName == 'membership') {
    CRM_Uimods_Tools_SearchTableAdjustments::adjustMembershipTable($objectName, $headers, $rows, $selector);
  } elseif ($objectName == 'event') {
    CRM_Uimods_Tools_SearchTableAdjustments::adjustEventTable($objectName, $headers, $rows, $selector);
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function uimods_civicrm_buildForm($formName, &$form) {
  // hook in the various renderers
  CRM_Uimods_Tools_MoneyFields::processBuildForm($formName, $form);
  CRM_Uimods_Tools_BankAccount::renderForm($formName, $form);
  CRM_Uimods_Tools_BirthYear::processBuildFormHook($formName, $form);
  CRM_Uimods_Tools_EmailReceipt::process_buildForm($formName, $form);
  switch ($formName) {
    case 'CRM_Contact_Form_Merge':
      CRM_Uimods_MergeFromUIMods::buildFormHook($formName, $form);
      break;

    case 'CRM_Member_Form_MembershipView':
      CRM_Uimods_ContractDownload::remove($form);
      break;

    case 'CRM_Activity_Form_Search':
    case 'CRM_Contact_Form_Search_Advanced':
      if ($form->elementExists('activity_role')) {
        $form->setDefaults(['activity_role' => 0]);
      }
  }
}

/**
 * Implements hook_civicrm_alterTemplateFile().
 *
 * Use modified templates for Membership and Contribution lists
 * If they stop working (after a CiviCRM upgrade):
 *  1) check if they have changed (compare checksums). If so:
 *  2) create a diff of our files vs. the original file (4.6.22)
 *  3) try to apply (patch) the original files and copy to extension
 */
function uimods_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  // ACTIVITIES:
  // modified versions based on CiviCRM 5.69.5
  //   CRM/Activity/Form/Search.tpl            SHA1: cd7118ad6e31b43a61d2b2609adc4e0a9525f188
  //   CRM/Contact/Form/Search/Advanced.tpl    SHA1: aa344d1add52e1b28f7eb9f5de8988af5a5ad6cb
  //   CRM/Activity/Form/Selector.tpl          SHA1: 7f1f3e650a8f03714469afc047c559385ff82bfa
  if ($tplName == 'CRM/Activity/Form/Search.tpl') {
    $tplName = 'CRM/Activity/Form/UimodsSearch.tpl';
  } elseif ($tplName == 'CRM/Contact/Form/Search/Advanced.tpl') {
    $tplName = 'CRM/Contact/Form/Search/UimodsAdvanced.tpl';
  }

  // MEMBERSHIPS:
  // modified version based on CiviCRM 6.63.3
  // Replaced core files:
  //  at.greenpeace.uimods/templates/CRM/Member/Form/Selector_Civi_5_63.tpl
  //  at.greenpeace.uimods/templates/CRM/Member/Form/Search_Civi_5_63.tpl
  //
  // Uimods files:
  //  at.greenpeace.uimods/templates/CRM/Member/Form/UimodsSelector.tpl
  //  at.greenpeace.uimods/templates/CRM/Member/Form/UimodsSearch.tpl
  if ($tplName == 'CRM/Member/Form/Search.tpl') {
    $tplName = 'CRM/Member/Form/UimodsSearch.tpl';
  }

  // modified version based on CiviCRM 4.6.22 -   SHA1: fa69538de32175029221af5583c25b3c607b5c22
  if ($tplName == 'CRM/Member/Page/Tab.tpl') {
    CRM_Uimods_Tools_SearchTableAdjustments::adjustMembershipTableSmarty();
    $tplName = 'CRM/Member/Page/UimodsTab.tpl';
  }

  // CONTRIBUTIONS:
  // modified version based on CiviCRM 4.6.22 -       SHA1: b885eb162c82557ed87535a7b940397492af12e4
  // also replaced CRM/Contribute/Form/Selector.tpl:  SHA1: 70ecb4911dfd57f685be1048f11feb3463d850de
  if ($tplName == 'CRM/Contribute/Form/Search.tpl') {
    $tplName = 'CRM/Contribute/Form/UimodsSearch.tpl';
  }

  // modified version based on CiviCRM 4.6.22         SHA1: 9f82712218a9a19aabfc0906c4afbcd6faf19ee7
  if ($tplName == 'CRM/Contribute/Page/Tab.tpl') {
    $tplName = 'CRM/Contribute/Page/UimodsTab.tpl';
  }

  if ($tplName == 'CRM/Contact/Form/Search/Advanced.tpl') {
    if (isset($form->_submitValues['component_mode'])
      && $form->_submitValues['component_mode'] == CRM_Contact_BAO_Query::MODE_CONTRIBUTE) {
      $modeValue = $form->getVar('_modeValue');
      if (!empty($modeValue['resultFile'])
        && $modeValue['resultFile'] == 'CRM/Contribute/Form/Selector.tpl') {
        CRM_Core_Smarty::singleton()->assign('resultFile', 'CRM/Contribute/Form/UimodsSelector.tpl');
      }
    }
  }

  // EVENTS:
  // modified version based on CiviCRM 5.37
  if ($tplName == 'CRM/Event/Page/ManageEvent.tpl') {
    $tplName = 'CRM/Event/Page/UimodsManageEvent.tpl';
  }
}

/**
 * implement the hook to customize the summary view
 */
function uimods_civicrm_pageRun( &$page ) {
  $page_name = $page->getVar('_name');

  // improve contact summary popup of DocumentFromSingleContact form:
  if ($page_name == 'CRM_Contact_Page_View_Summary') {
    CRM_Core_Region::instance('page-header')->add(array(
      'scriptUrl' => CRM_Uimods_ExtensionUtil::url('js/improveSummaryPopups.js'),
    ));
  }

  if ($page_name == 'CRM_Contact_Page_View_Summary') {

    // $emailIdsMap - it is hack to get email id by index of array
    // because in the core template doesn't use email id :(
    $emailIdsMap = [];
    $smarty = CRM_Core_Smarty::singleton();
    $emails = $smarty->get_template_vars('email');
    foreach ($emails as $key => $email) {
      $emailIdsMap[$key] =  $email['id'];
    }
    CRM_Core_Resources::singleton()->addVars('emailIdsMap', $emailIdsMap);
    CRM_Core_Region::instance('page-header')->add([
      'scriptUrl' => CRM_Uimods_ExtensionUtil::url('js/add_create_supportcase_links.js'),
    ]);
    CRM_Core_Resources::singleton()->addStyleFile('at.greenpeace.uimods', 'css/viewContactSummary.css');

    $script = file_get_contents(__DIR__ . '/js/summary_view.js');
    $script = str_replace('EXTENDED_DEMOGRAPHICS', CRM_Uimods_Config::getExtendedDemographicsGroupID(), $script);
    CRM_Core_Region::instance('page-header')->add(['script' => $script]);
  }

  if (in_array($page_name, ['CRM_Contact_Page_View_Summary', 'CRM_Contact_Page_Inline_Email', 'CRM_Contact_Page_Inline_Phone'])) {
    try {
      $supportId = (int) civicrm_api3('LocationType', 'getvalue', [
        'return' => 'id',
        'name' => 'support',
        'is_active' => 1,
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $supportId = NULL;
    }

    $uimods = array(
      'privacy' => $page->get_template_vars('privacy'),
      'supportId' => $supportId,
    );

    if ($page_name == 'CRM_Contact_Page_Inline_Email') {
      $uimods['email'] = $page->get_template_vars('email');
      $uimods['form'] = 'email';
    } elseif ($page_name == 'CRM_Contact_Page_Inline_Phone') {
      $uimods['phone'] = $page->get_template_vars('phone');
      $uimods['form'] = 'phone';
    } else {
      $uimods['email'] = $page->get_template_vars('email');
      $uimods['phone'] = $page->get_template_vars('phone');
      $uimods['form'] = 'both';
    }

    CRM_Core_Resources::singleton()->addVars('uimods', $uimods);
    CRM_Core_Region::instance('page-header')->add(array(
      'scriptUrl' => CRM_Uimods_ExtensionUtil::url('js/handle_icons.js'),
    ));
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function uimods_civicrm_config(&$config) {
  _uimods_civix_civicrm_config($config);
  // register replacement hooks and let them run as early as possible
  Civi::dispatcher()->addListener(
    'hook_civicrm_pre',
    'CRM_Uimods_SMS_Listener::pre',
    PHP_INT_MAX - 1
  );
  Civi::dispatcher()->addListener(
    'hook_civicrm_inboundSMS',
    'CRM_Uimods_SMS_Listener::inboundSMS',
    PHP_INT_MAX - 1
  );
  Civi::dispatcher()->addListener(
    'hook_civicrm_buildForm',
    ImproveActivityAssigneesField::class . '::run',
    PHP_INT_MAX - 1
  );
}

function uimods_civicrm_container(ContainerBuilder $container) {
  $container->addResource(new FileResource(__FILE__));
  $container->findDefinition('dispatcher')->addMethodCall('addListener',
    ['civi.token.eval', ['\Civi\Uimods\EvaluateTokens', 'run']]
  )->setPublic(TRUE);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function uimods_civicrm_install() {
  _uimods_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function uimods_civicrm_enable() {
  _uimods_civix_civicrm_enable();

  // update config
  include_once('CRM/Uimods/Config.php');
  CRM_Uimods_Config::updateConfig();
}

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 */
function uimods_civicrm_preProcess($formName, &$form) {
  if ($formName === 'CRM_Contact_Form_Merge') {
    // Re-add colour coding - sill not be required when issue is resolved.
    // https://github.com/civicrm/org.civicrm.shoreditch/issues/373
    CRM_Core_Resources::singleton()->addStyle('
    /* table row highlighting */
    .page-civicrm-contact-merge .crm-container table.row-highlight tr.crm-row-ok td{
       background-color: #EFFFE7 !important;
    }
    .page-civicrm-contact-merge .crm-container table.row-highlight .crm-row-error td{
       background-color: #FFECEC !important;
    }');
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 */
function uimods_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  CRM_Uimods_Tools_BirthYear::processValidateFormHook($formName, $fields, $files, $form, $errors);
  CRM_Uimods_Tools_DialogerId::processValidateForm($formName, $fields, $files, $form, $errors);
  CRM_Uimods_Tools_MoneyFields::processValidateForm($formName, $fields, $files, $form, $errors);
}

/**
 * Implementation of hook_civicrm_alterReportVar.
 *
 * @param $varType
 * @param $var
 * @param $reportForm
 *
 * @throws \CRM_Core_Exception
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterReportVar
 */
function uimods_civicrm_alterReportVar($varType, &$var, $reportForm) {
  if (CRM_Utils_Request::retrieve('revert', 'Boolean') && !CRM_Core_Permission::check('administer CiviCRM')) {
    CRM_Core_Session::setStatus(ts('You do not have permission to revert changes.'), ts('Permission Denied'), 'error');
    if ($cid = $reportForm->getVar('cid')) {
      if ($oid = $reportForm->getVar('oid')) {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/merge', "reset=1&cid={$cid}&oid={$oid}", FALSE, NULL, FALSE));
      } else {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', "reset=1&selectedChild=log&cid={$cid}", FALSE, NULL, FALSE));
      }
    } else {
      CRM_Utils_System::redirect(CRM_Report_Utils_Report::getNextUrl($reportForm->getVar('summary'), 'reset=1', FALSE, TRUE));
    }
  }
}

function uimods_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = [], $context = null) {
  if (isset($tokens['uimods']) && !empty(Civi::settings()->get('at_greenpeace_uimods_token_sql_task_id'))) {
    foreach ($cids as $cid) {
      try {
        civicrm_api3('Sqltask', 'execute', [
          'id' => Civi::settings()->get('at_greenpeace_uimods_token_sql_task_id'),
          'input_val' => json_encode(['contact_id' => $cid]),
          'log_to_file' => 1,
        ]);
        $uimodsTokens = UimodsToken::get(FALSE)
          ->addSelect('tokens')
          ->addWhere('contact_id', '=', $cid)
          ->execute()
          ->first();
        foreach ($uimodsTokens['tokens'] ?? [] as $key => $value) {
          $values[$cid]['uimods.' . $key] = $value;
        }
      } catch (Exception $e) {
        Civi::log()->warning("[UIMods] Encountered error while calculating tokens for contact {$cid}: {$e->getMessage()}");
      }
    }
  }
}

function uimods_civicrm_merge($type, &$data, $mainContactId = NULL, $secondaryContactId = NULL, $tables = NULL) {
  if ($type == 'form') {
    $mergeContact = MergeContact::getInstance();
    $mergeContact->setData($data['migration_info'], $mainContactId, $secondaryContactId);
  }

  if ($type == 'sqls') {
    $mergeContact = MergeContact::getInstance();
    $mergeContact->postMergeFixEmails();
    $mergeContact->postMergeFixPhones();

    $sqlList = $mergeContact->postMergeFixBirth();
    foreach ($sqlList as $sql) {
      $data[] = $sql;
    }
  }

  if ($type == 'sqls') {
    // remove UPDATE against civicrm_uimods_token as it would fail due to
    // the unique index on contact_id
    $data = array_filter($data, function($sql) {
      return strpos($sql, 'UPDATE civicrm_uimods_token') === FALSE;
    });
    // delete tokens for both records - they are re-created on demand anyway
    $data[] = CRM_Core_DAO::composeQuery(
      'DELETE FROM civicrm_uimods_token WHERE contact_id IN (%1, %2)',
      [
        1 => [$mainContactId, 'Integer'],
        2 => [$secondaryContactId, 'Integer']
      ]
    );
  }
}

function uimods_civicrm_summaryActions(&$actions, $contactID) {
  // add "open document with single contact" action
  $actions['uimods_open_document_with_single_contact'] = [
    'ref'         => 'uimods-civioffice-render-single',
    'title'       => ts('Create Document from Template'),
    'weight'      => 0,
    'key'         => 'uimods_open_document_with_single_contact',
    'class'       => 'medium-popup',
    'href'        => CRM_Utils_System::url('civicrm/uimods/document-from-single-contact', "reset=1"),
    'permissions' => ['view all contacts']
  ];
}

function uimods_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // Require 'manage tags' permission to create/update tags
  $permissions['tag']['create'] = ['manage tags'];
  $permissions['tag']['update'] = ['manage tags'];
}

/**
 * Throw exception when attempting to send disallowed email workflows to prevent
 * accidental email communication with supporters
 *
 * @param $params
 * @param $context
 *
 * @return void
 * @throws \Exception
 */
function uimods_civicrm_alterMailParams(&$params, $context) {
  if (!empty($params['workflow']) && $params['workflow'] != 'UNKNOWN') {
    foreach (Civi::settings()->get('allowed_email_workflows') ?? [] as $allowedWorkflow) {
      if (preg_match($allowedWorkflow, $params['workflow'])) {
        // workflow is allowed, continue
        return;
      }
    }
    throw new Exception("Attempting to send email workflow {$params['workflow']} which is not allowed. Please adjust the allowed_email_workflows setting to allow usage if needed.");
  }
}
