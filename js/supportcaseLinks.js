CRM.$(function ($) {
  initAddCreateSupportCaseLinks();

  function initAddCreateSupportCaseLinks() {
    $("#crm-email-content .crm-summary-row").each(function(index) {
      var rowElement = $(this);
      var linkElement = rowElement.find('.crm-contact_email > a');
      var emailId = findEmailId(rowElement);
      var urlParams = {};

      if (emailId !== undefined) {
        urlParams['prefill_email_id'] = emailId;
      }

      var newLink = CRM.url('civicrm/supportcase/add-case', urlParams);

      linkElement.removeClass('crm-popup');
      linkElement.addClass('uimod__send-email-button');
      linkElement.attr('target', '_blank');
      linkElement.attr('href', newLink);
    });
  }

  function findEmailId(rowElement) {
    // CRM.vars['emailIdsMap'] - it is hack to get email id by index(block id) of array
    // because in the core template doesn't use email id :(
    var hiddenElement = $(rowElement).find('.hiddenElement[id^="Email_Block_"]');
    var elementId = hiddenElement.attr('id');

    if (elementId !== undefined) {
      var blockId = elementId.split("_")[2];
      if (CRM.vars['emailIdsMap'][blockId] !== undefined) {
        return CRM.vars['emailIdsMap'][blockId];
      }
    }

    return undefined;
  }

});
