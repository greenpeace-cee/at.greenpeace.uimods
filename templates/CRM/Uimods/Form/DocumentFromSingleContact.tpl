{* Template based on "CRM/Civioffice/Form/DocumentFromSingleContact.tpl" *}
{crmScope extensionKey='de.systopia.civioffice'}
  <div class="crm-block crm-form-block">
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <div class="crm-section">
      <div class="label">{$form.document_uri.label}</div>
      <div class="content">
        <div class="uimods__manage_template-uri-input-wrap">
          <div class="uimods__manage_template-uri-input">
            {$form.document_uri.html}
          </div>
          <div class="uimods__manage_template-buttons">
            <div id="uimods_manage_template_save_button"></div>
            <div id="uimods_manage_template_toggle_checkbox"></div>
          </div>
        </div>
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
          <div class="label">{$form.activity_medium_id.label}</div>
          <div class="content">{$form.activity_medium_id.html}</div>
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
      initUimodsLiveTemplate();
      toggleActivityFieldMandatory();

      function initUimodsLiveTemplate() {
        var onHide = function (fieldElement) {fieldElement.closest('.crm-section').css('overflow', 'hidden').css('height', 0)};
        var onShow = function (fieldElement) {fieldElement.closest('.crm-section').css('overflow', 'visible').css('height', 'auto')};
        var templateParams = {
          'scopeName' :'DocumentFromSingleContact',
          'targetElement' : $('#document_uri'),
          'targetElementLabel' : 'Document',
          'toggleCheckboxParentElement' : $('#uimods_manage_template_toggle_checkbox').first(),
          'saveTemplateButtonParentElement': $('#uimods_manage_template_save_button').first(),
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
            'isHideFieldAsDefault' : true,
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

        // wysiwyg has bigger height than regular textarea, and it scrolls page to down
        // scrolls to top after loading wysiwyg:
        setTimeout(function () {
          var uiDialogContentElement = $('.CRM_Uimods_Form_DocumentFromSingleContact').closest('.ui-dialog-content');
          if (uiDialogContentElement.length !== 0) {
            uiDialogContentElement.scrollTop("0");
          } else {
            CRM.$(window).scrollTop(0);
          }
        }, 800);
      }

      function toggleActivityFieldMandatory() {
        $('.CRM_Uimods_Form_DocumentFromSingleContact').find('#activity_type_id').on('change', function (e) {
          checkActivityFieldsShouldBeRequired();
        });
        checkActivityFieldsShouldBeRequired();
      }

      /**
       * make activity medium and subject mandatory if activity_type_id is set
       */
      function checkActivityFieldsShouldBeRequired() {
        var required = $('.CRM_Uimods_Form_DocumentFromSingleContact').find('#activity_type_id').val() != '';
        $('.CRM_Uimods_Form_DocumentFromSingleContact').find('#activity_medium_id').attr('required', required);
        $('.CRM_Uimods_Form_DocumentFromSingleContact').find('#activity_subject').attr('required', required);
      }
    });
  </script>
  <style>
    .uimods__manage_template-uri-input {
      align-items: baseline;
      display: flex;
      gap: 10px;
    }

    .uimods__manage_template-uri-input-wrap {
      align-items: flex-start;
      display: flex;
      gap: 10px;
    }

    .uimods__manage_template-uri-input-wrap .crm-error {
      max-width: 300px;
    }

    .uimods__manage_template-buttons {
      gap: 10px;
      display: flex;
    }

    #uimods_manage_template_toggle_checkbox {
      display: flex;
    }
  </style>
{/literal}
