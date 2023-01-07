<?php

use CRM_MembershipExtras_Service_MembershipInstalmentsSchedule as InstalmentsSchedule;
use Civi\Api4\ItemmanagerPeriods;

class CRM_MembershipExtras_Helper_ItemManagerInstalmentSchedule {

  /**
   * Gets Instalment Details by given $schedule and membership ID
   * The instalment details include instalment_count, instalment_frequency
   * and instalment_frequency_unit
   *
   * @param $schedule
   * @param $membershipID
   *
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  public static function getInstalmentDetails($schedule, $membershipID) {
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
      $instalmentDetails['instalments_count'] = self::getInstalmentCountBySchedule($schedule);
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
    return $schedule == InstalmentsSchedule::QUARTERLY ? 3 : 1;
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
    return $interval == 1 && $schedule == InstalmentsSchedule::ANNUAL ? 'year' : 'month';
  }

  /**
   * Gets Instalment interval number by given schedule
   *
   * @param $schedule
   * @param DateTime|null $startDate start of the new membership
   * @param null $priceValues current selected price values
   * @return int
   */
  public static function getInstalmentCountBySchedule($schedule, DateTime $startDate = NULL, $priceValues = NULL) {

      # Can do nothing here
      if (empty($priceValues) || empty($startDate))
      {
          new API_Exception(ts('At least start date and and one price values must be selected.'));
          return 0;
      }

      $priceValueKeys = array_keys($priceValues);
      # we need to check a consistent period exception selection

      # we need to check a consistent period exception selection
      $noExceptionsCount = Civi\Api4\ItemmanagerSettings
          ::get()
          ->addWhere('price_field_value_id','IN',$priceValueKeys)
          ->addWhere('enable_period_exception','=',false)
          ->execute()
          ->count();

      $isExceptionsCount = Civi\Api4\ItemmanagerSettings
          ::get()
          ->addWhere('price_field_value_id','IN',$priceValueKeys)
          ->addWhere('enable_period_exception','=',true)
          ->execute()
          ->count();

      if ($noExceptionsCount > 0 && $isExceptionsCount > 0)
      {
          new API_Exception(ts('The selected set of price values belongs to different periods.'));
          return 0;
      }

      # start here to get the first setting (all records)
      $firstSetting = Civi\Api4\ItemmanagerSettings
        ::get()
          ->addWhere('price_field_value_id','IN',$priceValueKeys)
          ->execute()
          ->first();

      if (empty($firstSetting))
      {
          new API_Exception(ts('No ItemmanagerSettings has been found.'));
          return 0;
      }

      # now we need the period record
      $relatedPeriod = Civi\Api4\ItemmanagerPeriods
          ::get()
          ->addWhere('id','=',$firstSetting['itemmanager_periods_id'])
          ->execute()
          ->single();

      # now we can calculate the base amount
      $instalmentInterval = $firstSetting['enable_period_exception'] ?
                                $firstSetting['exception_periods']:$relatedPeriod['periods'];

      return $instalmentInterval;

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
      return InstalmentsSchedule::MONTHLY;
    }

    if ($frequencyUnit == 'month' && $frequencyInterval == 3 && $installmentsCount == 4) {
      return InstalmentsSchedule::QUARTERLY;
    }

    if ($frequencyUnit == 'year' && $frequencyInterval == 1 && $installmentsCount == 1) {
      return InstalmentsSchedule::ANNUAL;
    }
  }

}
