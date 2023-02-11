<?php

use CRM_MembershipExtras_Service_MembershipInstalmentsSchedule as InstalmentsScheduleService;
use Civi\Api4\ItemmanagerPeriods;
use Civi\Api4\ItemmanagerSettings;
use CRM_MembershipExtras_SettingsManager as SettingsManager;

class CRM_MembershipExtras_Helper_ItemManagerInstalmentSchedule {

  /**
   * Gets Instalment Details by given $schedule and membership ID
   * The instalment details include instalment_count, instalment_frequency
   * and instalment_frequency_unit
   *
   * @param $schedule
   * @param $membershipID
   * @param null $payments_storage retrieved data from user
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function getInstalmentDetails($schedule, $membershipID, $payments_storage = null) {
    $membershipType = civicrm_api3('Membership', 'get', [
      'sequential' => 1,
      'id' => $membershipID,
      'api.MembershipType.get' => [],
    ])['values'][0]['api.MembershipType.get']['values'][0];

    $durationUnit = $membershipType['duration_unit'];
    if ($membershipType['period_type'] == 'rolling' && ($durationUnit == 'lifetime' || $durationUnit == 'month')) {
      $instalmentDetails['instalments_count'] = 1;
    }
    else {
        if (empty($payments_storage))
            $instalmentDetails['instalments_count'] = self::getInstalmentCountBySchedule($schedule);
        else
            $instalmentDetails['instalments_count'] = count($payments_storage['payments']);
    }
    $instalmentDetails['instalments_frequency'] = self::getFrequencyInterval($schedule);
    $instalmentDetails['instalments_frequency_unit'] = self::getFrequencyUnit($schedule, $instalmentDetails['instalments_frequency']);

    return $instalmentDetails;
  }

  /**
   * Gets frequency interval by schedule
   *
   * For example, if schedule is quaterly interval shall be 3
   * if schedule is annual or monthly interval shall be 1.
   *
   * @param $schedule
   * @return int
   */
  public static function getFrequencyInterval($schedule) {
    return 1;
  }

  /**
   * Gets frequency unit by schedule and interval
   *
   * If schedule is annual and frequency is 1
   * the frequency unit shall be year and,
   * quarterly or monthly the frequeny unit
   * will always be month.
   *
   * @param $schedule
   * @param $interval
   * @return string
   */
  public static function getFrequencyUnit($schedule, $interval) {
    return 'month';
  }

  /**
   * Gets Instalment interval number by given schedule
   *
   * @param $schedule
   * @param DateTime|null $startDate start of the new membership
   * @param null $priceValues current selected price values
   * @return int
   */
  public static function getInstalmentCountBySchedule($schedule, DateTime $startDate = NULL, $priceValues = NULL):array {

      $instalmentData = array(
          'InstalmentInterval' => 0,
          'ItemManagerSettings' => NULL,
          'ItemManagerPeriod' => 0
      );

      # Can do nothing here
      if (empty($priceValues) || empty($startDate))
      {
          $errorResponse = [
              'is_error' => TRUE,
              'error_message' => ts('At least start date and and one price values must be selected.'),
          ];
          CRM_Core_Page_AJAX::returnJsonResponse($errorResponse);

          return $instalmentData;
      }



      $priceValueKeys = array_keys($priceValues);
      # we need to check a consistent period exception selection

      # we need to check a consistent period exception selection
      $noExceptionsCount = ItemmanagerSettings::get()
          ->addWhere('price_field_value_id','IN',$priceValueKeys)
          ->addWhere('enable_period_exception','=',false)
          ->execute()
          ->count();

      $isExceptionsCount = ItemmanagerSettings::get()
          ->addWhere('price_field_value_id','IN',$priceValueKeys)
          ->addWhere('enable_period_exception','=',true)
          ->execute()
          ->count();

      if ($noExceptionsCount > 0 && $isExceptionsCount > 0)
      {
          $errorResponse = [
              'is_error' => TRUE,
              'error_message' => ts('The selected set of price values belongs to different time intervals.'),
          ];
          CRM_Core_Page_AJAX::returnJsonResponse($errorResponse);

          return $instalmentData;
      }

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

          return $instalmentData;
      }

      # now we need the period record
      $relatedPeriod = ItemmanagerPeriods::get()
          ->addWhere('id','=',$firstSetting['itemmanager_periods_id'])
          ->execute()
          ->single();

      # now we can calculate the base amount
      $instalmentInterval = $firstSetting['enable_period_exception'] ?
                                $firstSetting['exception_periods']:$relatedPeriod['periods'];

      $instalmentData['InstalmentInterval'] = $instalmentInterval;
      $instalmentData['ItemManagerSettings'] = $firstSetting;
      $instalmentData['ItemManagerPeriod'] = $relatedPeriod;

      return $instalmentData;

  }

  /**
   * Checks if Payment Plan
   *
   * @return bool
   */
  public static function isPaymentPlanPayment() {
    $isSavingContribution = CRM_Utils_Request::retrieve('record_contribution', 'Int');
    $contributionIsPaymentPlan = CRM_Utils_Request::retrieve('contribution_type_toggle', 'String') === 'payment_plan';

    if ($isSavingContribution && $contributionIsPaymentPlan) {
      return TRUE;
    }

    return FALSE;
  }

  public static function getPaymentPlanSchedule($frequencyUnit, $frequencyInterval, $installmentsCount) {
    if ($frequencyUnit == 'month' && $frequencyInterval == 1 && $installmentsCount == 12) {
      return InstalmentsScheduleService::MONTHLY;
    }

    if ($frequencyUnit == 'month' && $frequencyInterval == 3 && $installmentsCount == 4) {
      return InstalmentsScheduleService::QUARTERLY;
    }

    if ($frequencyUnit == 'year' && $frequencyInterval == 1 && $installmentsCount == 1) {
      return InstalmentsScheduleService::ANNUAL;
    }
  }

}
