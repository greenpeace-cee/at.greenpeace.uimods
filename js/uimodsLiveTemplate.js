/*
Example of usage:

<script src="{crmResURL ext=at.greenpeace.uimods file=js/uimodsLiveTemplate.js}"></script>
{literal}
  <script>
    CRM.$(function ($) {
      CRM.uimodsLiveTemplate.init({
        'scopeName' :'DocumentFromSingleContact',
        'targetElement' : $('#document_uri'),
        'targetElementLabel' : 'Document',
        'toggleCheckboxParentElement' : $('#s2id_document_uri').closest('.content'),
        'saveTemplateButtonParentElement': $('.crm-submit-buttons').first(),
        'applyToFields': [
          {
            'id' : 'activity_type_id',
            'type' : 'select2',
            'onHide' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'hidden')},
            'onShow' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'visible')},
            'isHideFieldAsDefault' : true,
          },
          {
            'id' : "description",
            'type' : 'wysiwygElement',
            'onHide' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'hidden')},
            'onShow' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'visible')},
          },
          {
            'id' : 'activity_subject',
            'type' : 'textInput',
            'onHide' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'hidden')},
            'onShow' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'visible')},
          },
          {
            'id' : 'activity_attach_doc',
            'type' : 'checkbox',
            'onHide' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'hidden')},
            'onShow' : function (fieldElement) {fieldElement.closest('.crm-section').css('visibility', 'visible')},
          },
        ]
      });
    });
  </script>
{/literal}
*/

CRM.$(function ($) {
  CRM.uimodsLiveTemplate = {};
  CRM.uimodsLiveTemplate.params = {};

  CRM.uimodsLiveTemplate.init = function (params) {
    var scopeName = params.scopeName;
    CRM.uimodsLiveTemplate.params[scopeName] = params;
    CRM.uimodsLiveTemplate.params[scopeName]['uimodsTemplates'] = [];

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
          params.uimodsTemplates = uimodsTemplates;
          applyTemplateValues(uimodsTemplates, params);
          applyTemplateFieldsVisibilities(uimodsTemplates, params);
        }, function(failure) {
          console.error('UimodsTemplate(' + params.scopeName + ') error:');
          console.error(failure);
        });
      }
    });
  }

  function updateUimodsTemplateParams(params) {
    CRM.api4('UimodsTemplate', 'get', {
      where: [
        ["scope_name", "=", params.scopeName],
        ["target_value", "=", params.targetElement.val()]
      ],
    }).then(function(uimodsTemplates) {
      params.uimodsTemplates = uimodsTemplates;
    }, function(failure) {
      console.error('UimodsTemplate(' + params.scopeName + ') error:');
      console.error(failure);
    });
  }

  function applyTemplateValues(uimodsTemplates, params) {
    for (var template of uimodsTemplates) {
      var element = $('#' + template.field_name);
      if (element.length === 0) {
        continue;
      }

      if (template.field_type === 'select2') {
        element.val(template.field_value);
        element.select2();
      }

      if (template.field_type === 'checkbox') {
        element.prop("checked", template.field_value);
      }

      if (template.field_type === 'textInput') {
        element.val(template.field_value);
      }

      if (template.field_type === 'wysiwygElement') {
        CRM.wysiwyg.setVal(element, template.field_value);
      }
    }
  }

  function applyTemplateFieldsVisibilities(uimodsTemplates, params) {
    // hide all fields which is hidden as default
    for (var field of params.applyToFields) {
      if (field['isHideFieldAsDefault'] === true && field['onHide'] !== 'undefined') {
        var fieldElement = $('#' + field.id);
        field.onHide(fieldElement);
      }
    }

    for (var template of uimodsTemplates) {
      var element = $('#' + template.field_name);
      if (element.length === 0) {
        continue;
      }

      var fieldParams = findJsTemplateParams(template.field_name, params);
      if (fieldParams !== null) {
        if (template.is_field_hidden) {
          if (fieldParams['onHide'] !== 'undefined') {
            fieldParams.onHide(element);
          }
        } else {
          if (fieldParams['onShow'] !== 'undefined') {
            fieldParams.onShow(element);
          }
        }
      }
    }
  }

  function unHideFields(params) {
    for (var template of params.uimodsTemplates) {
      var element = $('#' + template.field_name);
      if (element.length === 0) {
        continue;
      }

      var fieldParams = findJsTemplateParams(template.field_name, params);
      if (fieldParams !== null) {
        if (fieldParams['onShow'] !== 'undefined') {
          fieldParams.onShow(element);
        }
      }
    }
  }

  function createIsEnabledUimodsTemplateCheckbox(params) {
    params.toggleCheckboxParentElement.append('' +
      '<div style="display: flex; gap: 10px;align-items: center;">' +
        '<label for="' + getIsEnabledUimodsTemplateCheckboxId(params) + '" style="margin-bottom: 0 !important;">Use Template?</label>' +
        '<input id="' + getIsEnabledUimodsTemplateCheckboxId(params) + '" type="checkbox" checked="checked" class="crm-form-checkbox">' +
      '</div>'
    );

    $('#' + getIsEnabledUimodsTemplateCheckboxId(params)).on('change', function() {
      if ($(this).prop("checked")) {
        applyTemplateFieldsVisibilities(params.uimodsTemplates, params);
      } else {
        unHideFields(params);
      }
    });
  }

  function isUimodsTemplateEnabled(params) {
    return $('#' + params.scopeName + 'isUimodsTemplateEnabled').prop("checked");
  }

  function getIsEnabledUimodsTemplateCheckboxId(params) {
    return params.scopeName + 'isUimodsTemplateEnabled';
  }

  function initTemplateSaveButton(params) {
    var buttonId = params.scopeName + 'uimodsTemplateSaveButton';

    params.saveTemplateButtonParentElement.append('' +
      '<a href="#" class="crm-link" id="' + buttonId + '"  title="Manage template"' +
        'style="display: flex; width: 30px; height:30px; font-size:23px; align-items: center; justify-content: center;">' +
        '<i class="crm-i fa-wrench"></i>' +
      '</a>'
    );

    $('#' + buttonId) .on("click", function (e) {
      e.preventDefault();
      if (params.targetElement.val() !== '') {
        var html = '<div>';
        html += '<div>';
        html += 'Template for ' + params.targetElementLabel + ' "' + params.targetElement.val() + '"';
        html += '</br>';
        html += '</br>';
        html += 'Current form values will be saved as the default value when using the template.';
        html += '</div>';
        html += '</br>';
        html += '<p>You can hide form elements that should not be used with this '  + params.targetElementLabel + ' by deselecting them here:</p>';

        for (var field of params.applyToFields) {
          var uiModsTemplateParam = findUiModsTemplateParams(field.id, params);
          var isFieldHidden = false;
          if (uiModsTemplateParam !== null) {
            isFieldHidden = uiModsTemplateParam.is_field_hidden;
          } else if (field['isHideFieldAsDefault'] === true) {
            isFieldHidden = true;
          }
          html += '<div>';
          html += '<input type="checkbox" id="' + generateIsFieldHiddenId(field.id, params) + '" ';
          if (!isFieldHidden) {
            html += ' checked="checked" ';
          }
          html += ' >';
          html += '<label for="' + generateIsFieldHiddenId(field.id, params) + '">' + field.id + '</label>';
          html += '</div>';
        }
        html += '</div>';
        html += '<p>(Checked fields will be shown)</p>';

        CRM.confirm({
          title: 'Manage Template',
          message: html,
        }).on('crmConfirm:yes', function() {
          for (var field of params.applyToFields) {
            if (field.type === 'select2') {
              var elementSelect2 = $('#' + field.id);
              var isSelect2FieldHidden = !$('#' + generateIsFieldHiddenId(field.id, params)).prop("checked");
              saveUimodsTemplate(isSelect2FieldHidden, field.id, elementSelect2.val(), field.type, params);
            }

            if (field.type === 'checkbox') {
              var elementCheckbox = $('#' + field.id);
              var isCheckboxFieldHidden = !$('#' + generateIsFieldHiddenId(field.id, params)).prop("checked");
              saveUimodsTemplate(isCheckboxFieldHidden, field.id, elementCheckbox.prop("checked") ? '1' : '0', field.type, params);
            }

            if (field.type === 'textInput') {
              var elementTextInput = $('#' + field.id);
              var isTextInputFieldHidden = !$('#' + generateIsFieldHiddenId(field.id, params)).prop("checked");
              saveUimodsTemplate(isTextInputFieldHidden, field.id, elementTextInput.val(), field.type, params);
            }

            if (field.type === 'wysiwygElement') {
              var wysiwygElement = $('#' + field.id);
              var isWysiwygElementHidden = !$('#' + generateIsFieldHiddenId(field.id, params)).prop("checked");
              saveUimodsTemplate(isWysiwygElementHidden, field.id, CRM.wysiwyg.getVal(wysiwygElement), 'wysiwygElement', params);
            }
          }

          // huck to update data current ui mods template params
          //TODO: update each params after updating each settings
          setTimeout(function () {
            updateUimodsTemplateParams(params);
          }, 800);
        })
      } else {
        CRM.status('Select a ' + params.targetElementLabel + ' to manage the template', 'error');
      }
    });
  }

  function saveUimodsTemplate(isFieldHidden, fieldName, fieldValue, fieldType, params) {
    CRM.api4('UimodsTemplate', 'UpdateTemplate', {
      scope_name: params.scopeName,
      target_value: params.targetElement.val(),
      field_name: fieldName,
      field_value: fieldValue,
      field_type: fieldType,
      is_field_hidden: isFieldHidden ? 1 : 0,
    }).then(function(results) {
      CRM.status('Template field "' + fieldName + '" saved!');
      applyTemplateFieldsVisibilities(results[0]['template'], params);
    }, function(failure) {
      CRM.status('Cannot save template for "' + fieldName + '"', 'error');
      console.error('Cannot save template for "' + fieldName + '"', 'error');
      console.error(failure);
    });
  }

  function findJsTemplateParams(fieldName, params) {
    for (var field of params.applyToFields) {
      if (field.id === fieldName) {
        return field;
      }
    }

    return null;
  }

  function findUiModsTemplateParams(fieldName, params) {
    for (var field of params.uimodsTemplates) {
      if (field.field_name === fieldName) {
        return field;
      }
    }

    return null;
  }

  function generateIsFieldHiddenId(fieldName, params) {
    return 'is_show_field_' + params.scopeName  + '_' + fieldName;
  }

});
