{include file="CRM/Civioffice/Form/DocumentFromSingleContact.tpl"}

{literal}
  <script>
    CRM.$(function ($) {
      initCkeditor();

      function initCkeditor() {
        var textareaElements = $("textarea[id^='live_snippets_']");
        if (textareaElements.length > 0) {
          textareaElements.each(function (i, element) {
            $(element).data('preset', 'uimods');
            CRM.wysiwyg.create(element);
          });
        }
      }
    });
  </script>
{/literal}
