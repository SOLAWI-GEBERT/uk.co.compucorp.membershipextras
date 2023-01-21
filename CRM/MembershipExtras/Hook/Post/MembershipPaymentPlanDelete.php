<?php
use CRM_MembershipExtras_Service_ManualPaymentProcessors as ManualPaymentProcessors;

/**
 * Implements post-process hooks on ContributionRecur entity.
 */
class CRM_MembershipExtras_Hook_Post_MembershipPaymentPlanDelete {


    /**
     * Operation being done on the line item
     *
     * @var string
     */
    private $operation;

    /**
     * ID of the record.
     *
     * @var int
     */
    private $id;

    /**
     * Reference to BAO.
     *
     * @var \CRM_Member_DAO_MembershipPayment
     */
    private $membershipPayment;

    /**
     * The membership that is attached
     * to this payment.
     *
     * @var array
     */
    private $membership;


  /**
   * CRM_MembershipExtras_Hook_Post_ContributionRecur constructor.
   *
   */
    public function __construct($operation, $objectId, CRM_Member_DAO_MembershipPayment $objectRef) {
        $this->operation = $operation;
        $this->id = $objectId;
        $this->membershipPayment = $objectRef;
        $this->setMembership();
    }

  /**
   * Post processes recurring contribution entity.
   */
  public function postProcess() {

  }


    private function setMembership() {
        $this->membership = civicrm_api3('Membership', 'get', [
            'sequential' => 1,
            'id' => $this->membershipPayment->membership_id,
            'return' => ['membership_type_id', 'is_override', 'status_override_end_date'],
        ])['values'][0];
    }

}
