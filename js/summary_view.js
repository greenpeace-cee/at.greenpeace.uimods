/*-------------------------------------------------------+
| Greenpeace UI Modifications                            |
| Copyright (C) 2017 SYSTOPIA                            |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

var uimods_extended_demographics = "#custom-set-content-EXTENDED_DEMOGRAPHICS";

/**
 * Make birth year group and demographics mutually data-dependent
 */
function birthday_data_dependencies() {
  // add demographic -> extended demographic dependency
  var current_value = cj("#crm-demographic-content").attr('data-dependent-fields');
  var fields = eval(current_value);
  if (fields) {
    if (fields.indexOf(uimods_extended_demographics) == -1) {
      fields.push(uimods_extended_demographics);
      cj("#crm-demographic-content").attr('data-dependent-fields', JSON.stringify(fields));
    }
  }

  // add extended demographic -> demographic dependency
  var current_value = cj(uimods_extended_demographics).attr('data-dependent-fields');
  var fields = [];
  if (current_value != undefined) {
    fields = eval(current_value);
  }
  if (fields) {
    if (fields.indexOf("#crm-demographic-content") == -1) {
      fields.push("#crm-demographic-content");
      cj(uimods_extended_demographics).attr('data-dependent-fields', JSON.stringify(fields));
    }
  }
}

/**
 * Toggle colored tags
 */
function toggle_tags() {
  var tag_without_color = "#tags .crm-tag-item:not([style*='background-color'])";

  cj(tag_without_color).hide();
  cj("#tagLink").append(
    '<div id="show-all-tags" style="cursor:pointer;">' + ts('Show all tags') + '</div>' +
    '<div id="show-colored-tags" style="display:none;cursor:pointer;">' + ts('Show colored tags') + '</div>'
  );

  cj("#show-all-tags").click(function () {
    cj(tag_without_color).show();
    cj("#show-all-tags, #show-colored-tags").toggle();
  });

  cj("#show-colored-tags").click(function () {
    cj(tag_without_color).hide();
    cj("#show-all-tags, #show-colored-tags").toggle();
  });

  cj("#tags").bind('DOMSubtreeModified', function () {
    if (cj("#show-all-tags").css('display') !== 'none') {
      cj(tag_without_color).hide();
    }
  });
}

cj(document).ready(function () {
  // inject birthday dependencies
  birthday_data_dependencies();

  // inject data dependency after reload
  cj(document).bind("ajaxComplete", birthday_data_dependencies);

  toggle_tags();
});

