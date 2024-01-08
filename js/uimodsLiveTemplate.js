/*
Example of usage:

CRM.uimodsLiveTemplate.init({
  'scopeName' :'DocumentFromSingleContact',
  'targetElement' : $('#document_uri'),
  'targetElementLabel' : 'Document',
  'toggleCheckboxParentElement' : $('#s2id_document_uri').closest('.content'),
  'saveTemplateButtonParentElement': $('.crm-submit-buttons').first(),
  'applyToFields': [
    {
      'id' : 'activity_type_id',
      'type' : 'select2'
    },
    {
      'selector' : "textarea[id^='live_snippets_']",
      'type' : 'wysiwygElements'
    },
    {
      'id' : "description",
      'type' : 'wysiwygElement'
    },
    {
      'id' : 'activity_subject',
      'type' : 'textInput'
    },
    {
      'id' : 'activity_attach_doc',
      'type' : 'checkbox'
    },
  ]
});
*/

CRM.$(function ($) {
  CRM.uimodsLiveTemplate = {};
  CRM.uimodsLiveTemplate.params = {};

  CRM.uimodsLiveTemplate.init = function (params) {
    var scopeName = params.scopeName;
    CRM.uimodsLiveTemplate.params[scopeName] = params;

    initOnChangeTargetElement(CRM.uimodsLiveTemplate.params[scopeName]);
    createIsEnabledUimodsTemplateCheckbox(CRM.uimodsLiveTemplate.params[scopeName]);
    initTemplateSaveButton(CRM.uimodsLiveTemplate.params[scopeName]);
  }

  function initOnChangeTargetElement(params) {
    params.targetElement.on("change", function (e) {
      if (isUimodsTemplateEnabled(params)) {
        CRM.api4('UimodsTemplate', 'get', {
          where: [
            ["scope_name", "=", params.scopeName],
            ["target_value", "=", params.targetElement.val()]
          ],
        }).then(function(uimodsTemplates) {
          applyTemplateValues(uimodsTemplates, params);
        }, function(failure) {
          console.error('UimodsTemplate(' + params.scopeName + ') error:');
          console.error(failure);
        });
      }
    });
  }

  function applyTemplateValues(uimodsTemplates, params) {
    for (var template of uimodsTemplates) {
      var element = $('#' + template.field_name);

      if (template.htmlType === 'select2') {
        if (element.length > 0) {
          element.val(template.field_value);
          element.select2();
        }
      }

      if (template.htmlType === 'checkbox') {
        if (element.length > 0) {
          element.prop("checked", template.field_value);
        }
      }

      if (template.htmlType === 'textInput') {
        if (element.length > 0) {
          element.val(template.field_value);
        }
      }

      if (template.htmlType === 'wysiwygElement') {
        if (element.length > 0) {
          CRM.wysiwyg.setVal(element, template.field_value);
        }
      }
    }
  }

  function createIsEnabledUimodsTemplateCheckbox(params) {
    params.toggleCheckboxParentElement.append('' +
      '<div style="display: flex; gap: 10px;align-items: center;padding-bottom: 10px;padding-top: 5px;">' +
        '<label for="' + params.scopeName + 'isUimodsTemplateEnabled" style="margin-bottom: 0 !important;">Use uimods Templates?</label>' +
        '<input id="' + params.scopeName + 'isUimodsTemplateEnabled" type="checkbox" checked="checked" class="crm-form-checkbox">' +
      '</div>'
    );
  }

  function initTemplateSaveButton(params) {
    var buttonId = params.scopeName + 'uimodsTemplateSaveButton';
    params.saveTemplateButtonParentElement.append('<button id="' + buttonId + '" class="crm-form-submit default validate crm-button" type="button">Save/update uimods template</button>');

    $('#' + buttonId) .on("click", function (e) {
      if (params.targetElement.val() !== '') {
        CRM.confirm({
          title: 'Save/update uimods template',
          message: 'Are you sure you want to update uimods templates for selected ' + params.targetElementLabel + '(' + params.targetElement.val() + ')',
        }).on('crmConfirm:yes', function() {

          for (var field of params.applyToFields) {
            if (field.type === 'select2') {
              var elementSelect2 = $('#' + field.id);
              saveUimodsTemplate(field.id, elementSelect2.val(), field.type, params);
            }

            if (field.type === 'checkbox') {
              var elementCheckbox = $('#' + field.id);
              saveUimodsTemplate(field.id, elementCheckbox.prop("checked") ? '1' : '0', field.type, params);
            }

            if (field.type === 'textInput') {
              var elementTextInput = $('#' + field.id);
              saveUimodsTemplate(field.id, elementTextInput.val(), field.type, params);
            }

            if (field.type === 'wysiwygElements') {
              var wysiwygElements = $(field.selector);
              wysiwygElements.each(function (i, element) {
                saveUimodsTemplate($(element).attr('id'), CRM.wysiwyg.getVal(element), 'wysiwygElement', params);
              });
            }

            if (field.type === 'wysiwygElement') {
              var wysiwygElement = $('#' + field.id);
              saveUimodsTemplate(field.id, CRM.wysiwyg.getVal(wysiwygElement), 'wysiwygElement', params);
            }
          }
        })
      } else {
        CRM.status('To save uimods template need to chose ' + params.targetElementLabel + '.', 'error');
      }
    });
  }

  function saveUimodsTemplate(fieldName, fieldValue, fieldType, params) {
    CRM.api4('UimodsTemplate', 'UpdateTemplate', {
      scope_name: params.scopeName,
      target_value: params.targetElement.val(),
      field_name: fieldName,
      field_value: fieldValue,
      field_type: fieldType,
      is_field_hidden: '0',
    }).then(function(results) {
      CRM.status('Uimods template("' + fieldName + '") saved!')
    }, function(failure) {
      CRM.status('Cannot save uimods template("' + fieldName + '")', 'error');
      console.error('Cannot save uimods template("' + fieldName + '")', 'error');
      console.error(failure);
    });
  }

  function isUimodsTemplateEnabled(params) {
    return $('#' + params.scopeName + 'isUimodsTemplateEnabled').prop("checked");
  }

});
