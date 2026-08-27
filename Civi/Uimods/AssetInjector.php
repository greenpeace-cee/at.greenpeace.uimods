<?php

namespace Civi\Uimods;

use CRM_Core_Region;
use CRM_Uimods_ExtensionUtil;
use CRM_Core_Resources;

class AssetInjector {

  public static function addScript($scriptUrl, $location = 'page-header'): void {
    CRM_Core_Region::instance($location)->add([
      'scriptUrl' => CRM_Uimods_ExtensionUtil::url($scriptUrl),
    ]);
  }

  public static function addScriptInline($scriptUrl, $location, $replaces): void {
    $file = CRM_Uimods_ExtensionUtil::path($scriptUrl);
    if (!file_exists($file)) {
      return;
    }

    $scriptContent = file_get_contents($file);
    foreach ($replaces as $replaceFrom => $replaceTo) {
      $scriptContent = str_replace($replaceFrom, $replaceTo, $scriptContent);
    }

    CRM_Core_Region::instance($location)->add(['script' => $scriptContent]);
  }

  public static function addCssStyles($cssStylesUrl): void {
    CRM_Core_Resources::singleton()->addStyleFile('at.greenpeace.uimods', $cssStylesUrl);
  }

}
