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

use CRM_Uimods_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Uimods_Form_AssignContribution extends CRM_Core_Form {

  public function buildQuickForm() {
    $contribution_id = CRM_Utils_Request::retrieve('cid', 'Integer');
    if (!is_numeric($contribution_id)) {
      throw new Exception("Invalid contribution id given.", 1);
    }

    // add some hidden attributes
    $this->add('hidden', 'cid', $contribution_id);

    // get the available memberships
    $membership_options = $this->getMemberships($contribution_id);
    if (empty($membership_options)) {
      CRM_Core_Session::setStatus("The contact has no memberships!", "Error", "error");
    }

    // see if it's currently assigned
    $this->assign('assigned_to', $this->getCurrentlyAssignedMembership($contribution_id));

    // add form elements
    $this->add(
      'select',
      'membership_id',
      E::ts('Select Membership'),
      $membership_options,
      TRUE,
      array('class' => 'crm-select2 huge')
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Assign'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    parent::buildQuickForm();
  }



  public function postProcess() {
    $values = $this->exportValues();

    if (!empty($values['cid'])) {
      if (!empty($values['membership_id'])) {
        // ok: first remove any old link
        $old_link = $this->getCurrentlyAssignedMembership($values['cid']);
        if ($old_link) {
          // doesn't exist: civicrm_api3('MembershipPayment', 'delete', array('id' => $old_link));
          CRM_Member_BAO_MembershipPayment::del($old_link);
        }

        // the assign the membership
        civicrm_api3('MembershipPayment', 'create', array(
          'contribution_id' => $values['cid'],
          'membership_id'   => $values['membership_id'],
        ));
        CRM_Core_Session::setStatus("Contribution assigned to membership.", "Success", "info");

      } else {
        CRM_Core_Session::setStatus("No membership selected!", "Error", "error");
      }
    } else {
      CRM_Core_Session::setStatus("No contribution ID given!", "Error", "error");
    }


    // CRM_Core_Session::setStatus(E::ts('You picked color "%1"', array(
    //   1 => $options[$values['favorite_color']],
    // )));
    parent::postProcess();
  }

  /**
   * Get the id of the current assignment
   */
  protected function getCurrentlyAssignedMembership($contribution_id) {
    $membership_payment = civicrm_api3('MembershipPayment', 'get', array('contribution_id' => $contribution_id));
    if (!empty($membership_payment['id'])) {
      return $membership_payment['id'];
    } else {
      return NULL;
    }
  }

  /**
   * Get all the contact's memberships
   */
  protected function getMemberships($contribution_id) {
    $membership_options = array();

    $contribution = civicrm_api3('Contribution', 'getsingle', array(
      'id'     => $contribution_id,
      'return' => 'contact_id'));

    $memberships = civicrm_api3('Membership', 'get', array(
      'contact_id'   => $contribution['contact_id'],
      'sequential'   => 1,
      'is_test'      => 0,
      'option.sort'  => 'end_date DESC, start_date DESC, id DESC',
      'option.limit' => 0,
    ));

    $status_list = civicrm_api3('MembershipStatus', 'get', array(
      'sequential'   => 0,
      'option.limit' => 0,
    ))['values'];

    foreach ($memberships['values'] as $membership) {
      $status = $status_list[$membership['status_id']];
      $membership_options[$membership['id']] = "{$membership['membership_name']} [{$membership['id']}] ({$status['label']})";

      // add dates
      if (!empty($membership['start_date'])) {
        $membership_options[$membership['id']] .= " from {$membership['start_date']}";
      }

      if (!empty($membership['end_date'])) {
        $membership_options[$membership['id']] .= " until {$membership['end_date']}";
      }

      if (empty($status['is_active'])) {
        $membership_options[$membership['id']] = '(inactive) ' . $membership_options[$membership['id']];
      }
    }

    return $membership_options;
  }
}
