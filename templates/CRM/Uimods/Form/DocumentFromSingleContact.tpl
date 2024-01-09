{* Template based on "CRM/Civioffice/Form/DocumentFromSingleContact.tpl" *}
{crmScope extensionKey='de.systopia.civioffice'}
  <div class="crm-block crm-form-block">
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <div class="crm-section">
      <div class="label">{$form.document_uri.label}</div>
      <div class="content">
        {$form.document_uri.html}
        <div id="uimods_manage_template"></div>
      </div>
      <div class="clear"></div>
    </div>

    <div class="crm-section">
      <div class="label">{$form.document_renderer_uri.label}</div>
      <div class="content">{$form.document_renderer_uri.html}</div>
      <div class="clear"></div>
    </div>

    <div class="crm-section">
      <div class="label">{$form.target_mime_type.label}</div>
      <div class="content">{$form.target_mime_type.html}</div>
      <div class="clear"></div>
    </div>

    <div class="crm-accordion-wrapper">
      <div class="crm-accordion-header">{ts}Activity{/ts}</div>

      <div class="crm-accordion-body">

        <div class="crm-section">
          <div class="label">{$form.activity_type_id.label}</div>
          <div class="content">{$form.activity_type_id.html}</div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">{$form.activity_subject.label}</div>
          <div class="content">{$form.activity_subject.html}</div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">{$form.activity_attach_doc.label}</div>
          <div class="content">{$form.activity_attach_doc.html}</div>
          <div class="clear"></div>
        </div>
      </div>
    </div>

    {include file="CRM/Civioffice/Form/LiveSnippets.tpl"}

    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
{/crmScope}

<script src="{crmResURL ext=at.greenpeace.uimods file=js/uimodsLiveTemplate.js}"></script>

{literal}
  <script>
    CRM.$(function ($) {
      initCkeditor();
      window.setTimeout(initUimodsLiveTemplate, 500);

      function initUimodsLiveTemplate() {
        var onHide = function (fieldElement) {fieldElement.closest('.crm-section').css('overflow', 'hidden').css('height', 0)};
        var onShow = function (fieldElement) {fieldElement.closest('.crm-section').css('overflow', 'visible').css('height', 'auto')};
        var templateParams = {
          'scopeName' :'DocumentFromSingleContact',
          'targetElement' : $('#document_uri'),
          'targetElementLabel' : 'Document',
          'toggleCheckboxParentElement' : $('#s2id_document_uri').closest('.content'),
          'saveTemplateButtonParentElement': $('#uimods_manage_template').first(),
          'applyToFields': [
            {
              'id' : 'activity_type_id',
              'type' : 'select2',
              'onHide' : onHide,
              'onShow' : onShow,
            },
            {
              'id' : 'document_renderer_uri',
              'type' : 'select2',
              'onHide' : onHide,
              'onShow' : onShow,
            },
            {
              'id' : 'target_mime_type',
              'type' : 'select2',
              'onHide' : onHide,
              'onShow' : onShow,
            },
            {
              'id' : 'activity_subject',
              'type' : 'textInput',
              'onHide' : onHide,
              'onShow' : onShow,
            },
            {
              'id' : 'activity_attach_doc',
              'type' : 'checkbox',
              'onHide' : onHide,
              'onShow' : onShow,
            },
          ]
        };

        $("textarea[id^='live_snippets_']").each(function (i, element) {
          var fieldId = $(element).attr('id');
          templateParams.applyToFields.push({
            'type' : 'wysiwygElement',
            'id' : fieldId,
            'onHide' : onHide,
            'onShow' : onShow,
          });
        });

        CRM.uimodsLiveTemplate.init(templateParams);
      }

      function initCkeditor() {
        var textareaElements = $("textarea[id^='live_snippets_']");
        textareaElements.each(function (i, element) {
          $(element).data('preset', 'uimods');
          CRM.wysiwyg.create(element);
        });
      }
    });
  </script>
{/literal}
