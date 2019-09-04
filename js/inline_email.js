cj(document).ready(function () {

  var html = '' +
    '<span ' +
    'class="fa fa-trash" ' +
    'style="float:right;height:16px;width:16px;font-size:14px;color:#CB0001;" ' +
    'title="' + ts('Email on hold - generally due to bouncing.') + '"' +
    '></span>';

  cj("#crm-email-content .crm-summary-row .crm-label").each(function(i, obj) {
    if (CRM.vars['uimods']['email'][i+1]['on_hold'] !== '0' && CRM.vars['uimods']['privacy']['do_not_email'] !== '0') {
      cj(obj).append(html);
    }
    else if (CRM.vars['uimods']['email'][i+1]['on_hold'] !== '0') {
      cj(obj).find('.icon').remove();
      cj(obj).append(html);
    }
  });

});