cj(document).ready(function () {

  var html = '' +
    '<span ' +
    'class="fa fa-exclamation" ' +
    'style="height:16px;padding:0 2px;font-size:14px;color:#CB0001;" ' +
    'title="' + ts('Email on hold - generally due to bouncing.') + '"' +
    '></span>';
  var supportIcon = '' +
    '<span ' +
    'class="fa fa-medkit" ' +
    'style="height:16px;padding:0 2px;font-size:14px;color:#0071be;" ' +
    'title="' + ts('This email address may only be used for support-related communication.') + '"' +
    '></span>';

  cj("#crm-email-content .crm-summary-row .crm-label").each(function(i, obj) {
    if (typeof CRM.vars['uimods']['email'][i+1] == 'undefined') {
      return;
    }
    if (CRM.vars['uimods']['email'][i+1]['on_hold'] !== '0' && CRM.vars['uimods']['privacy']['do_not_email'] !== '0') {
      cj(obj).siblings('.crm-contact_email').prepend(html);
    }
    else if (CRM.vars['uimods']['email'][i+1]['on_hold'] !== '0') {
      cj(obj).find('.icon').remove();
      cj(obj).siblings('.crm-contact_email').prepend(html);
    }
    if (CRM.vars['uimods']['email'][i+1]['location_type_id'] == CRM.vars['uimods']['supportId']) {
      cj(obj).siblings('.crm-contact_email').prepend(supportIcon);
    }
  });

});
