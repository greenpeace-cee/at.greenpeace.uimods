<?php
namespace Civi\Api4\Action\UimodsTemplate;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\UimodsTemplate;
use CRM_Core_Session;
use CRM_Uimods_BAO_UimodsTemplate;

class UpdateTemplate extends AbstractAction {

  /**
   * @var string
   */
  public $scope_name;

  /**
   * @var string
   */
  public $target_value;

  /**
   * @var string
   */
  public $field_name;

  /**
   * @var string
   */
  public $field_value;

  /**
   * @var string
   */
  public $field_type;

  /**
   * @var bool
   */
  public $is_field_hidden;

  public function _run(Result $result) {
    $template = UimodsTemplate::save(FALSE)
      ->setRecords([
        [
          'scope_name' => $this->scope_name,
          'target_value' => $this->target_value,
          'field_name' => $this->field_name,
          'field_value' => $this->field_value,
          'field_type' => $this->field_type,
          'is_field_hidden' => $this->is_field_hidden,
          'contact_id' => CRM_Core_Session::getLoggedInContactID(),
          'updated_at' => date('Y-m-d H:i:s'),
        ],
      ])
      ->setMatch(['scope_name', 'field_name', 'target_value'])
      ->execute();

    $result[] = ['message' => 'Template is updated!', 'template' => $template];
  }

}
