<?php

/**
 * Trait CRM_MembershipExtras_Helper_PaymentPlanTogglerTrait
 */
trait CRM_MembershipExtras_Helper_PaymentPlanTogglerTrait {

  /**
   * @param $region
   */
  private function addResources($region) {

      $resourceManager = CRM_Core_Resources::singleton();
      if ( $resourceManager->ajaxPopupsEnabled) {
          $resourceManager->addScriptFile('uk.co.compucorp.membershipextras', 'js/paymentPlanToggler.js', 999, $region);
      }

  }

}
