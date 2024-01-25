CRM.$(function ($) {
  improveCreateDocumentFromTemplatePopup();

  function improveCreateDocumentFromTemplatePopup() {
    var popup = $('.crm-action-uimods-civioffice-render-single > a');
    var settings = popup.data('popup-settings');
    if (settings === undefined || settings === '') {
      settings = {};
    }

    var onDialogOpenCallback = function (e) {
      var popupMainElement = $(this).closest('.ui-dialog');

      pseudoOnLoad(function () {
        return popupMainElement.find('button[data-identifier="_qf_DocumentFromSingleContact_submit_preview"]').length > 0;
      }, function () {
        var downloadButton = popupMainElement.find('button[data-identifier="_qf_DocumentFromSingleContact_submit_preview"]');
        downloadButton.css('background', 'rgb(58 58 58)');
        downloadButton.on( "click", function() {
            var isThereFormErrors = popupMainElement.find('.CRM_Uimods_Form_DocumentFromSingleContact .label label.crm-error').length > 0;
            if (!isThereFormErrors) {
              CRM.alert(ts('Document preview was downloaded successfully. No activity has been saved yet.'), ts("Preview Downloaded"), "success");
              CRM.status('Preview Downloaded', 'success');
            }
          }
        );

        var cancelButton = popupMainElement.find('button[data-identifier="_qf_DocumentFromSingleContact_cancel"]');
        cancelButton.css('background', 'white');
        cancelButton.css('color', 'black');

        var saveAndDownloadButton = popupMainElement.find('button[data-identifier="_qf_DocumentFromSingleContact_upload"]');
        saveAndDownloadButton.on( "click", function() {
          var isThereFormErrors = popupMainElement.find('.CRM_Uimods_Form_DocumentFromSingleContact .label label.crm-error').length > 0;
          if (!isThereFormErrors) {
            CRM.alert(ts('Document was downloaded successfully (and attached to the activity).'), ts("Document Downloaded (and Saved)"), "success");
            CRM.status('Document Downloaded (and Saved)', 'success');
          }
        });

      });
    };

    settings['dialog'] = {};
    settings['dialog']['title'] = ts('Create Document from Template');
    settings['dialog']['open'] = onDialogOpenCallback;

    popup.data('popup-settings', settings);
  }

  function pseudoOnLoad(isLoadedFunction, onLoadCallback) {
    var maxCountTries = 15;
    var countTries = 0;
    var checkEveryMicroseconds= 300;

    var interval = setInterval(function () {
      if (isLoadedFunction()) {
        onLoadCallback();
        clearInterval(interval);
      } else {
        countTries++;
        if (countTries > maxCountTries) {
          clearInterval(interval);
        }
      }
    }, checkEveryMicroseconds);
  }

});
