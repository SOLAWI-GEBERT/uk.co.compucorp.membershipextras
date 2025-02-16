<script type="text/javascript">
  {literal}
  CRM.$(function ($) {
    {/literal}
    const togglerValue = '{$contribution_type_toggle}';
    const currencySymbol = '{$currency_symbol}';
    console.info("Wie fangen hier an!")
    paymentPlanToggler(togglerValue, currencySymbol);
    {literal}
  });
  {/literal}
</script>
<div id="payment_plan_fields_tabs">
  <input name="contribution_type_toggle" type="hidden">
  <input name="payment_plan_datastorage" id="payment_plan_datastorage" type="hidden">
  {if $enable_paymentplan_period_selector}
  <div class="ui-tabs">

    <ul class="ui-tabs-nav ui-helper-clearfix">
      <li class="ui-corner-top ui-tabs-active" data-selector="contribution">
        <a href="#contribution-subtab">{ts}Contribution{/ts}</a>
      </li>
      <li class="ui-corner-top" data-selector="payment_plan">
        <a href="#payment_plan-subtab">{ts}Payment Plan{/ts}</a>
      </li>
    </ul>
  </div>
  {else}
    <div class="ui-tabs">

      <ul class="ui-tabs-nav ui-helper-clearfix">
        <li class="ui-corner-top ui-tabs-active" data-selector="contribution">
          <a href="#contribution-subtab" data-selector="contribution">{ts}Single Delivery{/ts}</a>
        </li>
        <li class="ui-corner-top" data-selector="payment_plan">
          <a class="button" href="#payment_plan-subtab" data-selector="payment_plan">{ts}Regular Deliveries{/ts}</a>
        </li>
      </ul>
    </div>
  {/if}
</div>
<table id="payment_plan_fields" >
  <tr id="payment_plan_schedule_row">
    <td class="label" nowrap>
      {$form.payment_plan_schedule.label}
    </td>
    <td nowrap>
      {$form.payment_plan_schedule.html}
    </td>
  </tr>
  <tr id="payment_plan_schedule_instalment_row">
    <td class="label" nowrap><label>{ts}Instalment Schedule{/ts}</label></td>
    <td>
      <div id="instalment_schedule_table"> </div>
    </td>
  </tr>
</table>
