<?php

use CRM_MembershipExtras_ExtensionUtil as E;
use CRM_MembershipExtras_SettingsManager as SettingsManager;

/**
 * Implements form changes needed to be done to add payment plan as an option to
 * pay for a membership.
 */
class CRM_MembershipExtras_Hook_BuildForm_MembershipPaymentPlan {

  use CRM_MembershipExtras_Helper_PaymentPlanTogglerTrait;

  /**
   * @var string
   *   Path where template with new fields is stored.
   */
  private $templatePath;

  /**
   * @var \CRM_Member_Form_Membership
   *   Form object that is being altered.
   */
  private $form;

  /**
   * CRM_MembershipExtras_Hook_BuildForm_Membership constructor.
   *
   * @param \CRM_Member_Form $form
   */
  public function __construct(CRM_Member_Form &$form) {
    $this->form = $form;
    $this->templatePath = CRM_MembershipExtras_ExtensionUtil::path() . '/templates';
  }

  /**
   * Implements changes to form loaded in $this->form
   */
  public function buildForm() {
    $this->addPaymentPlanSection();
  }

  /**
   * Adds new fields to the form and includes template that shows the fields and
   * juggles around exiting ones.
   */
  private function addPaymentPlanSection() {
    if ($this->form->_mode === 'live') {
      return;
    }

    $payment_plan_attr = !SettingsManager::getAllowItemmanager() ? [] : ['disable', 'readonly'];


    $paymentToggler =
      CRM_Utils_Request::retrieve('contribution_type_toggle', 'String', $this->form, FALSE);
    $this->form->assign('contribution_type_toggle', $paymentToggler ?: 'contribution');
    $this->form->add('select', 'payment_plan_schedule', E::ts('Schedule'), [], FALSE, $payment_plan_attr);
    # here a hidden type to store dynamically values from the sub page
    $this->form->assign('payment_plan_datastorage',"{'payments':{} }");
    $this->form->assign('payment_plan_reverse',false);
    $notUsesItemManager = !SettingsManager::getAllowItemmanager();
    $this->form->assign('enable_paymentplan_period_selector',$notUsesItemManager);
    CRM_Core_Region::instance('page-body')->add([
      'template' => "{$this->templatePath}/CRM/Member/Form/PaymentPlanToggler.tpl",
    ]);

    $this->addResources('html-header');
  }

}
