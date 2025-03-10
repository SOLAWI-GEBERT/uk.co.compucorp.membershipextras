<?php

/**
 * Helps manage settings for the extension.
 */
class CRM_MembershipExtras_SettingsManager {

  /**
   * Constant variables for settings use in Membership Type
   * The membership_extras_membership_type_settings key should be used
   * for all adding settings required for Membership Type
   */
  const MEMBERSHIP_TYPE_SETTINGS_KEY = 'membership_extras_membership_type_settings';

  /**
   * Returns the details of the default payment processor as per payment plan
   * settings, or NULL if it does not exist.
   *
   * @return int
   */
  public static function getDefaultProcessorID() {
    return self::getSettingValue('membershipextras_paymentplan_default_processor');
  }

  /**
   * Returns the 'days to renew in advance'
   * setting.
   *
   * @return int
   */
  public static function getDaysToRenewInAdvance() {
    $daysToRenewInAdvance = self::getSettingValue('membershipextras_paymentplan_days_to_renew_in_advance');
    if (empty($daysToRenewInAdvance)) {
      $daysToRenewInAdvance = 0;
    }

    return $daysToRenewInAdvance;
  }

  public static function getCustomFieldsIdsToExcludeForAutoRenew() {
    $customGroupsIdsToExcludeForAutoRenew = self::getSettingValue('membershipextras_customgroups_to_exclude_for_autorenew');
    if (empty($customGroupsIdsToExcludeForAutoRenew)) {
      return [];
    }

    $customFieldsToExcludeForAutoRenew = civicrm_api3('CustomField', 'get', [
      'return' => ['id'],
      'sequential' => 1,
      'custom_group_id' => ['IN' => $customGroupsIdsToExcludeForAutoRenew],
      'options' => ['limit' => 0],
    ]);
    if (empty($customFieldsToExcludeForAutoRenew['values'])) {
      return [];
    }

    $customFieldsIdsToExcludeForAutoRenew = [];
    foreach ($customFieldsToExcludeForAutoRenew['values'] as $customField) {
      $customFieldsIdsToExcludeForAutoRenew[] = $customField['id'];
    }

    return $customFieldsIdsToExcludeForAutoRenew;
  }


  /**
   * Gets Update start date renewal configuration
   *
   * @return int
   */
  public static function getUpdateStartDateRenewal() {
    $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_update_start_date_renewal');
    if (empty($updateStartDateRenewal)) {
      $updateStartDateRenewal = 0;
    }

    return $updateStartDateRenewal;
  }

    /**
     * Gets Update start date renewal configuration
     *
     * @return int
     */
    public static function getAllowItemmanager() {
        $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_use_itemmanager_as_period_source');
        if (empty($updateStartDateRenewal)) {
            $updateStartDateRenewal = 0;
        }

        return $updateStartDateRenewal;
    }

    /**
     * Gets Update start date renewal configuration
     *
     * @return int
     */
    public static function getDisableMail() {
        $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_disable_automatic_response_mail');
        if (empty($updateStartDateRenewal)) {
            $updateStartDateRenewal = 0;
        }

        return $updateStartDateRenewal;
    }

    /**
     * Gets disable renew
     *
     * @return int
     */
    public static function getDisableRenew() {
        $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_disable_autorenew');
        if (empty($updateStartDateRenewal)) {
            $updateStartDateRenewal = 0;
        }

        return $updateStartDateRenewal;
    }

    /**
     * Gets enabled fixation
     *
     * @return int
     */
    public static function getEnableFixedDay() {
        $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_enable_fixed_startdate');
        if (empty($updateStartDateRenewal)) {
            $updateStartDateRenewal = 0;
        }

        return $updateStartDateRenewal;
    }

    /**
     * Gets Fixed day settings
     *
     * @return int
     */
    public static function getFixedDay() {
        $updateStartDateRenewal = self::getSettingValue('membershipextras_paymentplan_fixed_day');
        if (empty($updateStartDateRenewal)) {
            $updateStartDateRenewal = 0;
        }

        return $updateStartDateRenewal;
    }

  public static function getMembershipTypeSettings(int $membershipTypeId) {
    $settings = [];
    $membershipTypeSettings = Civi::settings()->get(self::MEMBERSHIP_TYPE_SETTINGS_KEY);
    if (!isset($membershipTypeSettings)) {
      return $settings;
    }
    foreach ($membershipTypeSettings as $id => $settingFields) {
      if ($id == $membershipTypeId) {
        $settings = $settingFields;
      }
    }

    return $settings;
  }


  private static function getSettingValue($settingName) {
    $result = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => [$settingName],
    ]);

    if (isset($result['values'][0][$settingName])) {
      return $result['values'][0][$settingName];
    }

    return NULL;
  }

}
