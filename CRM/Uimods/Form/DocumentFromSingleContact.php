<?php

use CRM_Uimods_ExtensionUtil as E;

/**
 * Custom form based on de.systopia.civioffice/CRM/Civioffice/Form/DocumentFromSingleContact.php
 */
class CRM_Uimods_Form_DocumentFromSingleContact extends CRM_Core_Form {

  /**
   * The ID of the contact to create a document for.
   *
   * @var integer $contactId
   */
  public $contactId = null;

  public function getTitle() {
    return E::ts('CiviOffice - Generate a Single Document');
  }

  public function preProcess() {
    if (!CRM_Uimods_Utils_Extension::isCiviofficeEnable()) {
      throw new CRM_Core_Exception('de.systopia.civioffice is required extension!');
    }

    $this->contactId = CRM_Utils_Request::retrieve('cid', 'Int', $this, true);
    if (!CRM_Uimods_Utils_Contact::isExist($this->contactId)) {
      throw new CRM_Core_Exception('Contact doesn\'t exist');
    }

    $this->assign('user_id', $this->contactId);
  }

  public function buildQuickForm() {
    $defaults = [
      'activity_type_id' => CRM_Uimods_Utils_Civioffice::getContactSettingsCreateSingleActivityType(),
      'activity_attach_doc' => TRUE,
    ];
    $this->setAttribute('data-no-ajax-submit', 'true');

    $this->add('select', 'document_renderer_uri', E::ts("Document Renderer"), CRM_Uimods_Utils_Civioffice::getDocumentRendererList(), true, ['class' => 'crm-select2 huge']);
    $this->add('select2', 'document_uri', E::ts("Document"), CRM_Uimods_Utils_Civioffice::getDocumentList(), true, ['class' => 'huge', 'placeholder' => E::ts('- select -')]);
    $this->add('select', 'target_mime_type', E::ts("Target document type"), CRM_Uimods_Utils_Civioffice::getMimetypes(), true, ['class' => 'crm-select2']);
    $this->add('text', 'activity_subject', E::ts("Activity Subject"), ['size' => CRM_Utils_Type::HUGE], false);
    $this->add('select', 'activity_type_id', E::ts("Create Activity"), CRM_Civioffice_Configuration::getActivityTypes(), false, ['class' => 'crm-select2', 'placeholder' => E::ts("- don't create activity -")]);
    $this->add('checkbox', 'activity_attach_doc', E::ts("Attach Rendered Document"));
    $this->add('select', 'activity_medium_id', E::ts("Activity Medium"), CRM_Case_PseudoConstant::encounterMedium(), false, ['class' => 'crm-select2', 'placeholder' => E::ts('- select -')]);

    // Add fields for Live Snippets.
    CRM_Civioffice_LiveSnippets::addFormElements($this);

    // Set default values.
    $this->setDefaults($defaults);

    // add buttons
    $this->addButtons(
      [
        [
          'type' => 'upload',
          'name' => ts('Save and Download'),
          'isDefault' => true,
          'icon' => 'fa-download',
        ],
        [
          'type' => 'submit',
          'name' => ts('Download'),
          'subName' => 'preview',
          'icon' => 'fa-search',
          'isDefault' => false,
        ],
        [
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ],
      ]
    );
  }

  public function postProcess()
  {
    // TODO: Do not filter live snippet values.
    $values = $this->exportValues();

    // Extract and store live snippet values.
    $live_snippets = CRM_Civioffice_LiveSnippets::get('name');
    $live_snippet_values = CRM_Civioffice_LiveSnippets::getFormElementValues($this);
    $render_result = civicrm_api3('CiviOffice', 'convert', [
      'document_uri' => $values['document_uri'],
      'entity_ids' => [$this->contactId],
      'entity_type' => 'contact',
      'renderer_uri' => $values['document_renderer_uri'],
      'target_mime_type' => $values['target_mime_type'],
      'live_snippets' => $live_snippet_values,
    ]);

    $result_store_uri = $render_result['values'][0];
    $result_store = CRM_Civioffice_Configuration::getDocumentStore($result_store_uri);
    /* @var CRM_Civioffice_Document[] $rendered_documents */
    $rendered_documents = $result_store->getDocuments();
    if ($this->isLiveMode()) {
      // Create activity, if requested.
      if (!empty($values['activity_type_id'])) {
        $activityParams = [
          'activity_type_id' => $values['activity_type_id'],
          'subject' => !empty($values['activity_subject']) ? $values['activity_subject'] : E::ts("Document (CiviOffice)"),
          'status_id' => 'Completed',
          'activity_date_time' => date("YmdHis"),
          'target_id' => [$this->contactId],
          'details' => '<p>' . E::ts(
              'Created from document: %1',
              [1 => '<code>' . CRM_Civioffice_Configuration::getConfig()->getDocument($values['document_uri'])->getName() . '</code>']
            ) . '</p>'
            . '<p>' . E::ts('Live Snippets used:') . '</p>'
            . (!empty($live_snippet_values) ? '<table><tr>' . implode(
                '</tr><tr>',
                array_map(function ($name, $value) use ($live_snippets) {
                  return '<th>' . $live_snippets[$name]['label'] . '</th>'
                    . '<td>' . $value . '</td>';
                }, array_keys($live_snippet_values), $live_snippet_values)
              ) . '</tr></table>' : ''),
        ];

        if (!empty($values['activity_medium_id'])) {
          $activityParams['medium_id'] = $values['activity_medium_id'];
        }

        $activity = civicrm_api3('Activity', 'create', $activityParams);

        // generate & link attachment if requested
        if (!empty($values['activity_attach_doc'])) {
          foreach ($rendered_documents as $document) {
            /* @var \CRM_Civioffice_Document $document */
            $path_of_local_copy = $document->getLocalTempCopy();
            // attach rendered document
            $attachments = [
              'attachFile_1' => [
                'location' => $path_of_local_copy,
                'type' => $document->getMimeType(),
              ],
            ];
            // TODO: Use the "Attachment.create" API for permanently moving files, as without it, the
            //       temporary file might get deleted.
            CRM_Core_BAO_File::processAttachment($attachments, 'civicrm_activity', $activity['id']);
          }
        }
      }
    }

    // Store default values for activity type and attachment selection in current contact's settings.
    CRM_Uimods_Utils_Civioffice::setContactSettingsCreateSingleActivityAttachment(($values['activity_attach_doc'] ?? 0));
    CRM_Uimods_Utils_Civioffice::setContactSettingsCreateSingleActivityType(($values['activity_type_id'] ?? ''));

    switch (count($rendered_documents)) {
      case 0: // something's wrong
        throw new Exception(E::ts("Rendering Error!"));

      case 1: // single document -> direct download
        /** @var \CRM_Civioffice_Document $rendered_document */
        $rendered_document = reset($rendered_documents);
        $rendered_document->download();

      default:
        $result_store->downloadZipped();
    }
  }

  /**
   * Is the form in live mode (as opposed to being run as a preview).
   *
   * @return bool
   */
  protected function isLiveMode(): bool {
    return strpos($this->controller->getButtonName(), '_preview') === false;
  }

}
