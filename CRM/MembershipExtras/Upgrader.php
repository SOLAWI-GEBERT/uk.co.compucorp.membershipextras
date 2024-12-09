<?php

/**
 * Manages and configure entities during installation, uninstallation,
 * enabling and disabling the extension. Also includes the code
 * to run the upgrade steps defined in Upgrader/Steps/ directory.
 */
class CRM_MembershipExtras_Upgrader extends CRM_Extension_Upgrader_Base {

  public function postInstall() {
    // steps that create new entities.
    $creationSteps = [
      new CRM_MembershipExtras_Setup_Manage_OfflineAutoRenewalScheduledJob(),
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessorType(),
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessor(),
      new CRM_MembershipExtras_Setup_Manage_PaymentPlanActivityTypes(),
      new CRM_MembershipExtras_Setup_Manage_FutureMembershipStatusRules(),
    ];
    foreach ($creationSteps as $step) {
      $step->create();
    }

    // steps that configure existing entities or alter settings.
    $configurationSteps = [
      new CRM_MembershipExtras_Setup_Configure_SetManualPaymentProcessorAsDefaultProcessor(),
      new CRM_MembershipExtras_Setup_Configure_DisableContributionCancelActionsExtension(),
    ];
    foreach ($configurationSteps as $step) {
      $step->apply();
    }
  }

  public function enable() {
    $steps = [
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessorType(),
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessor(),
      new CRM_MembershipExtras_Setup_Manage_FutureMembershipStatusRules(),
      new CRM_MembershipExtras_Setup_Manage_PaymentPlanActivityTypes(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_PaymentPlanExtraAttributes(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_OfflineAutorenewOption(),
    ];
    foreach ($steps as $step) {
      $step->activate();
    }
  }

  public function disable() {
    $steps = [
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessor(),
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessorType(),
      new CRM_MembershipExtras_Setup_Manage_OfflineAutoRenewalScheduledJob(),
      new CRM_MembershipExtras_Setup_Manage_FutureMembershipStatusRules(),
      new CRM_MembershipExtras_Setup_Manage_PaymentPlanActivityTypes(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_PaymentPlanExtraAttributes(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_OfflineAutorenewOption(),
    ];
    foreach ($steps as $step) {
      $step->deactivate();
    }
  }

  public function uninstall() {
    $removalSteps = [
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessor(),
      new CRM_MembershipExtras_Setup_Manage_ManualPaymentProcessorType(),
      new CRM_MembershipExtras_Setup_Manage_OfflineAutoRenewalScheduledJob(),
      new CRM_MembershipExtras_Setup_Manage_PaymentPlanActivityTypes(),
      new CRM_MembershipExtras_Setup_Manage_FutureMembershipStatusRules(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_PaymentPlanExtraAttributes(),
      new CRM_MembershipExtras_Setup_Manage_CustomGroup_OfflineAutorenewOption(),
    ];
    foreach ($removalSteps as $step) {
      $step->remove();
    }
  }



  /**
   * This is a callback for running step upgraders from the queue
   *
   * @param CRM_Queue_TaskContext $context
   * @param \object $step
   *
   * @return true
   *   The queue requires that true is returned on successful upgrade, but we
   *   use exceptions to indicate an error instead.
   */
  public static function runStepUpgrade($context, $step) {
    $step->apply();
    return TRUE;
  }

  /**
   * Gets the PEAR style classname from an upgrader file
   *
   * @param $file
   *
   * @return string
   */
  private function getUpgraderClassnameFromFile($file) {
    $file = str_replace(realpath(__DIR__ . '/../../'), '', $file);
    $file = str_replace('.php', '', $file);
    $file = str_replace('/', '_', $file);
    return ltrim($file, '_');
  }

}
