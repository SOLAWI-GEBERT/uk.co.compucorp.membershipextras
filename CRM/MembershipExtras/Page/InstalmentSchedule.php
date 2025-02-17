<?php

use Civi\Api4\ItemmanagerPeriods;
use Civi\Api4\ItemmanagerSettings;
use CRM_MembershipExtras_SettingsManager as SettingsManager;
class CRM_MembershipExtras_Page_InstalmentSchedule extends CRM_Core_Page {

    function __construct($title = NULL, $mode = NULL)
    {
        parent::__construct($title, $mode);
        $this->_backward_search = array();
    }

    public function run() {
    $this->assignInstalments();
    $this->assignCurrencySymbol();
    $this->assignTaxTerm();

    parent::run();
  }

  private function assignInstalments() {
    $membershipTypeId = CRM_Utils_Request::retrieve('membership_type_id', 'Int');
    $priceFieldValues = CRM_Utils_Request::retrieve('price_field_values', 'String');

    $params = [];
    $action = 'getbymembershiptype';
    if (isset($membershipTypeId)) {
      $params['membership_type_id'] = $membershipTypeId;
      $params['reverse'] = FALSE;
    }
    elseif (isset($priceFieldValues)) {
      $params['price_field_values'] = ['IN' => $priceFieldValues];
      $action = 'getbypricefieldvalues';

        if ( SettingsManager::getAllowItemmanager())
        {


            $priceValueKeys = array_keys($priceFieldValues);
# start here to get the first setting (all records)
            $firstSetting = ItemmanagerSettings::get()
                ->addWhere('price_field_value_id','IN',$priceValueKeys)
                ->execute()
                ->first();

            if (empty($firstSetting))
            {
                $errorResponse = [
                    'is_error' => TRUE,
                    'error_message' => ts('No ItemmanagerSettings has been found. Inform administrator.'),
                ];
                CRM_Core_Page_AJAX::returnJsonResponse($errorResponse);

            }

            # now we need the period record
            $relatedPeriod = ItemmanagerPeriods::get()
                ->addWhere('id','=',$firstSetting['itemmanager_periods_id'])
                ->execute()
                ->single();

            $params['reverse'] = (bool)$relatedPeriod['reverse'];
        }
    }

    $params['schedule'] = CRM_Utils_Request::retrieve('schedule', 'String');
    $params['payment_method'] = CRM_Utils_Request::retrieve('payment_method', 'Int');
    $params['start_date'] = CRM_Utils_Request::retrieve('start_date', 'String');
    $params['join_date'] = CRM_Utils_Request::retrieve('join_date', 'String');


    try {
      $result = civicrm_api3('PaymentSchedule', $action, $params);

      $this->assign('instalments', $result['values']['instalments']);
      $this->assign('sub_total', $result['values']['sub_total']);
      $this->assign('tax_amount', $result['values']['tax_amount']);
      $this->assign('total_amount', $result['values']['total_amount']);
      $this->assign('membership_start_date', $result['values']['membership_start_date']);
      $this->assign('membership_end_date', $result['values']['membership_end_date']);

      $this->_backward_search = $result['values']['instalments'];
      Civi::resources()->addVars('membershipextras_paymentplan',$this->_backward_search);
      Civi::resources()->addVars('membershipextras_paymentplan_reverse',array($params['reverse']));

      if (isset($result['values']['prorated_number']) && isset($result['values']['prorated_unit'])) {
        $this->assign('prorated_number', $result['values']['prorated_number']);
        if ($result['values']['prorated_unit'] == CRM_MembershipExtras_Service_MembershipPeriodType_FixedPeriodTypeCalculator::BY_DAYS) {
          $this->assign('prorated_unit', ts('days'));
        }
        else {
          $this->assign('prorated_unit', ts('months'));
        }
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      $errorResponse = [
        'is_error' => TRUE,
        'error_message' => $e->getMessage(),
      ];
      CRM_Core_Page_AJAX::returnJsonResponse($errorResponse);
    }
  }

  private function assignCurrencySymbol() {
    $currencySymbol = CRM_Core_BAO_Country::defaultCurrencySymbol();
    $this->assign('currency_symbol', $currencySymbol);
  }

  private function assignTaxTerm() {
    $taxTerm = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => ["tax_term"],
    ])['values'][0]['tax_term'];
    $this->assign('tax_term', $taxTerm);
  }

}
