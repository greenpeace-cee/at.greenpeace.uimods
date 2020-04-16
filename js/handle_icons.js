cj(document).ready(function () {

  var html = '' +
    '<span ' +
    'class="fa fa-exclamation" ' +
    'style="float:right;height:16px;padding:0 1px;font-size:14px;color:#CB0001;" ' +
    'title="' + ts('Email on hold - generally due to bouncing.') + '"' +
    '></span>';
  var supportIcon = '' +
    '<span ' +
    'class="fa fa-medkit" ' +
    'style="float:right;height:16px;padding:0 1px;font-size:14px;color:#0071be;" ' +
    'title="' + ts('This contact detail may only be used for support-related communication.') + '"' +
    '></span>';
  var changeEmailLabelWidth = false;
  var changePhoneLabelWidth = false;

  if (CRM.vars['uimods']['email'] != null && (CRM.vars['uimods']['form'] === 'email' || CRM.vars['uimods']['form'] === 'both')) {
    cj("#crm-email-content .crm-summary-row .crm-label").each(function (i, obj) {
      if (typeof CRM.vars['uimods']['email'][i + 1] == 'undefined') {
        return;
      }
      if (CRM.vars['uimods']['email'][i + 1]['on_hold'] !== '0' && CRM.vars['uimods']['privacy']['do_not_email'] !== '0') {
        cj(obj).append(html);
      } else if (CRM.vars['uimods']['email'][i + 1]['on_hold'] !== '0') {
        cj(obj).find('.icon').remove();
        cj(obj).append(html);
      }
      if (CRM.vars['uimods']['email'][i + 1]['location_type_id'] == CRM.vars['uimods']['supportId']) {
        cj(obj).append(supportIcon);
      }
      if (CRM.vars['uimods']['email'][i + 1]['location_type_id'] == CRM.vars['uimods']['supportId'] && CRM.vars['uimods']['privacy']['do_not_email'] !== '0') {
        changeEmailLabelWidth = true;
      }
    });
  }

  if (CRM.vars['uimods']['phone'] != null && (CRM.vars['uimods']['form'] === 'phone' || CRM.vars['uimods']['form'] === 'both')) {
    cj("#crm-phone-content .crm-summary-row .crm-label").each(function (i, obj) {
      if (typeof CRM.vars['uimods']['phone'][i + 1] == 'undefined') {
        return;
      }
      if (CRM.vars['uimods']['phone'][i + 1]['location_type_id'] == CRM.vars['uimods']['supportId']) {
        cj(obj).append(supportIcon);
        changePhoneLabelWidth = true;
      }
    });
  }

  if (changeEmailLabelWidth) {
    cj('#crm-email-content .crm-label').css('width', '146px');
  }
  if (changePhoneLabelWidth) {
    cj('#crm-phone-content .crm-label').css('width', '158px');
  }

});
