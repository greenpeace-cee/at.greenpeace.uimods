<?php
namespace Civi\Api4;

/**
 * UimodsTemplate entity.
 *
 * Provided by the UI Modifications extension.
 *
 * @package Civi\Api4
 */
class UimodsTemplate extends Generic\DAOEntity {

  public static function permissions() {
    return [
      'default'    => ['view all contacts'],
    ];
  }
}
