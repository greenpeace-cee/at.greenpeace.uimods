CRM.$(function($) {
  var contractLink = CRM.$('[id^="membership_general"] div.crm-accordion-body table:nth-child(3) a');
  if (contractLink) {
    contractLink.replaceWith(CRM.$('<td class="html-adjust">' + contractLink.text() + '</td>'));
  }
});
