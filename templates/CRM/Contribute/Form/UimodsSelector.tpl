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
{include file="CRM/common/pager.tpl" location="top"}

{strip}
<div class="crm-contact-contribute-contributions">
<table class="selector row-highlight">
  <thead class="sticky">
  <tr>
    {if !$single and $context eq 'Search' }
      <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th>
    {/if}
    {if !$single}
      <th scope="col"></th>
    {/if}
    {foreach from=$columnHeaders item=header}
      <th scope="col">
      {if $header.sort}
        {assign var='key' value=$header.sort}
        {$sort->_response.$key.link}
      {else}
        {$header.name}
      {/if}
      </th>
    {/foreach}
  </tr>
  </thead>

  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}
  <tr id="rowid{$row.contribution_id}" class="{cycle values="odd-row,even-row"}{if $row.contribution_cancel_date} cancelled{/if} crm-contribution_{$row.contribution_id}">
    {if !$single }
        {if $context eq 'Search' }
          {assign var=cbName value=$row.checkbox}
          <td>{$form.$cbName.html}</td>
   {/if}
    <td>{$row.contact_type}</td>
      <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
    {/if}
    {if $row.contribution_soft_credit_amount}
      <td class="right bold crm-contribution-amount">&nbsp;</td>
    {else}
      <td class="right bold crm-contribution-amount"><span class="nowrap">{$row.total_amount|crmMoney:$row.currency}</span> {if $row.amount_level }<br /> ({$row.amount_level}){/if}
      {if $row.contribution_recur_id}
  <br /> {ts}(Recurring Contribution){/ts}
      {/if}
      </td>
    {/if}
    {if $softCreditColumns}
      <td class="right bold crm-contribution-soft_credit_amount"><span class="nowrap">{$row.contribution_soft_credit_amount|crmMoney:$row.currency}</span></td>
    {/if}
    <td class="crm-contribution-type crm-contribution-type_{$row.financial_type_id} crm-financial-type crm-financial-type_{$row.financial_type_id}">{$row.financial_type}</td>
    <td class="crm-contribution-source">{$row.contribution_source}</td>
    <td class="crm-contribution-receive_date">{$row.receive_date|crmDate}
      {* GP-22895 show revenue recognition date below contribution receive date *}
      {if !empty($row.revenue_recognition_date)}
      <br>
      <span title="Revenue Date for BMF">
        <i class="crm-i fa-info-circle"></i>
        &nbsp;{$row.revenue_recognition_date|crmDate}
      </span>
      {/if}
    </td>
    <td class="crm-contribution-payment_instrument">{$row.payment_instrument}</td>
    <td class="crm-contribution-donor_ba">{$row.donor_ba}</td>
    <td class="crm-contribution-status">
        {$row.contribution_status}<br />
        {if $row.contribution_cancel_date}
        {$row.contribution_cancel_date|crmDate}
        {/if}
    </td>
    {if $softCreditColumns}
      <td class="crm-contribution-soft_credit_name"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contribution_soft_credit_contact_id`"}">{$row.contribution_soft_credit_name}</a></td>
      <td class="crm-contribution-soft_credit_type">{$row.contribution_soft_credit_type}</td>
    {/if}
    <td>{$row.action|replace:'xx':$row.contribution_id}</td>
  </tr>
  {/foreach}

</table>
</div>
{/strip}

{include file="CRM/common/pager.tpl" location="bottom"}
