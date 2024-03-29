{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* Search form and results for Contributions *}
{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show'"}
<div class="crm-block crm-form-block crm-contribution-search-form-block">
  <div class="crm-accordion-wrapper crm-contribution_search_form-accordion {if $rows}collapsed{/if}">
      <div class="crm-accordion-header crm-master-accordion-header">
          {ts}Edit Search Criteria{/ts}
       </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body">
        {strip}
          <table class="form-layout">
            <tr>
              <td>{$form.buttons.html}</td>
            </tr>
            <tr>
              <td class="font-size12pt" colspan="2">
                <label>{$form.sort_name.label}</label><br />
                {$form.sort_name.html|crmAddClass:'twenty'}
              </td>
            </tr>
            <tr>
              {if $form.contact_tags}
                <td>
                  <label>{ts}Contributor Tag(s){/ts}</label><br />
                  {$form.contact_tags.html}
                </td>
              {else}
                <td>&nbsp;</td>
              {/if}

              {if $form.group}
                <td>
                  <label>{ts}Contributor Group(s){/ts}</label><br />
                  {$form.group.html}
                </td>
              {else}
                <td>&nbsp;</td>
              {/if}
            </tr>
{include file="CRM/Contribute/Form/Search/Common.tpl"}
            <tr>
               <td colspan="2">{$form.buttons.html}</td>
            </tr>
            </table>
        {/strip}
      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
{if $rowsEmpty || $rows}
<div class="crm-content-block">
{if $rowsEmpty}
<div class="crm-results-block crm-results-block-empty">
    {include file="CRM/Contribute/Form/Search/EmptyResults.tpl"}
</div>
{/if}

{if $rows}
    <div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. *}
        {* This section handles form elements for action task select and submit *}
        <div class="crm-search-tasks crm-event-search-tasks">
            {include file="CRM/common/searchResultTasks.tpl" context="Contribution"}
        </div>

        {* This section displays the rows along and includes the paging controls *}
  <div id="contributionSearch" class="crm-search-results">
        {include file="CRM/Contribute/Form/UimodsSelector.tpl" context="Search"}
  </div>
    {* END Actions/Results section *}
    </div>
{/if}

</div>
{/if}

