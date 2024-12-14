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
     * Reference
     *
     */
    private $params;

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
    public function __construct($operation, $objectId, $params) {
        $this->operation = $operation;
        $this->id = $objectId;
        $this->params = &$params;
        $this->setMembership();
    }

  /**
   * Post processes recurring contribution entity.
   */
  public function postProcess() {

      # do nothing if we are not meant
      if (! $this->checkMembershipPaymentPlan()) return;

      $transaction = new CRM_Core_Transaction();

      $params = [
          'membership_id' => $this->id,
          'options' => array(
              'limit' => 100,
              'sort' => "id DESC",
          ),
      ];


      try{
          $result = civicrm_api3('MembershipPayment', 'get', $params);
      }
      catch (CiviCRM_API3_Exception $e) {
          // Handle error here.
          $errorMessage = $e->getMessage();
          $errorCode = $e->getErrorCode();
          $errorResponse = [
              'is_error' => 1,
              'error_message' => $errorMessage,
              'error_code' => $errorCode,
          ];

          CRM_Core_Page_AJAX::returnJsonResponse($errorResponse);
      }

      $payments = $result['values'];

      foreach ($payments As $contribution_link) {


          CRM_Price_BAO_LineItem::deleteLineItems($this->id,'civicrm_membership');
          CRM_Price_BAO_LineItem::deleteLineItems((int)$contribution_link['contribution_id'],'civicrm_contribution');


          CRM_Contribute_BAO_Contribution::deleteContribution((int)$contribution_link['contribution_id']);
          $result = civicrm_api3('MembershipPayment', 'getcount', [
              'id' => (int)$contribution_link['id'],
          ]);
          if($result != 0) CRM_Member_BAO_MembershipPayment::deleteRecord($contribution_link);

      }

          $transaction->commit();


  }


    /**
     * Checks if membership was last payed for with a payment plan.
     *
     * @return bool
     */
    private function checkMembershipPaymentPlan() {
        $query = '
      SELECT civicrm_contribution_recur.id AS recurid
      FROM civicrm_membership_payment
      INNER JOIN civicrm_contribution ON civicrm_membership_payment.contribution_id = civicrm_contribution.id
      LEFT JOIN civicrm_contribution_recur ON civicrm_contribution.contribution_recur_id = civicrm_contribution_recur.id
      WHERE civicrm_membership_payment.membership_id = %1
      AND civicrm_contribution_recur.installments > 0
      ORDER BY civicrm_contribution.id DESC
      LIMIT 1
    ';
        $pendingContributionsResult = CRM_Core_DAO::executeQuery($query, [
            1 => [$this->id, 'Integer'],
        ]);
        $pendingContributionsResult->fetch();

        if (!empty($pendingContributionsResult->recurid)) {
            $this->recurringContribution = civicrm_api3('ContributionRecur', 'getsingle', [
                'id' => $pendingContributionsResult->recurid,
            ]);

            return TRUE;
        }

        return FALSE;
    }


    private function setMembership() {
        $this->membership = civicrm_api3('Membership', 'get', [
            'sequential' => 1,
            'id' => $this->id,
            'return' => ['membership_type_id', 'is_override', 'status_override_end_date'],
        ])['values'][0];
    }

}
