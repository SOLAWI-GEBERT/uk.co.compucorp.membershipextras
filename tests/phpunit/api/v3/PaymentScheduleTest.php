<?php

use CRM_MembershipExtras_Test_Fabricator_MembershipType as MembershipTypeFabricator;
use CRM_MembershipExtras_Test_Fabricator_PriceSet as PriceSetFabricator;
use CRM_MembershipExtras_Test_Fabricator_PriceField as PriceFieldFabricator;
use CRM_MembershipExtras_Test_Fabricator_PriceFieldValue as PriceFieldValueFabricator;

/**
 * Class PaymentScheduleTest
 *
 * @group headless
 */
class PaymentScheduleTest extends BaseHeadlessTest {

  /**
   * Test ExceptionIsThrownIfScheduleIsNotValid
   * @throws CiviCRM_API3_Exception
   */
  public function testExceptionIsThrownIfScheduleIsNotValid() {
    $this->expectException(CiviCRM_API3_Exception::class);
    civicrm_api3('PaymentSchedule', 'getByMembershipType', [
      'sequential' => 1,
      'membership_type_id' => 1,
      'schedule' => 'xyz',
    ])['values'];
  }

  /**
   * Test GetMonthlyRollingMembershipTypeSchedule
   * @throws CiviCRM_API3_Exception
   */
  public function testGetByMembershipType() {
    $membershipType = MembershipTypeFabricator::fabricate([
      'name' => 'Rolling Membership Type',
      'period_type' => 'rolling',
      'duration_unit' => 'year',
      'minimum_fee' => 120,
      'duration_interval' => 1,
    ]);

    $scheduleInstalment = civicrm_api3('PaymentSchedule', 'getByMembershipType', [
      'sequential' => 1,
      'membership_type_id' => $membershipType['id'],
      'schedule' => 'monthly',
    ])['values'];

    $this->assertCount(12, $scheduleInstalment);

    $currencySymbol = CRM_Core_BAO_Country::defaultCurrencySymbol();
    $expectedAmount = $currencySymbol . 10;
    $expectedTaxAmount = $currencySymbol . 0;

    $startDate = CRM_Member_BAO_MembershipType::getDatesForMembershipType(
      $membershipType['id'],
      NULL,
      NULL,
      NULL
    )['start_date'];

    $instalmentDate = new DateTime($startDate);

    foreach ($scheduleInstalment as $instalment) {
      $this->assertEquals($expectedAmount, $instalment['instalment_amount']);
      $this->assertEquals($expectedTaxAmount, $instalment['instalment_tax_amount']);
      $this->assertEquals($instalmentDate->format('Y-m-d'), $instalment['instalment_date']);
      $instalmentDate->add(new DateInterval('P1M'));
    }
  }

  public function testExceptionIsThrownIfOperatorIsNotIN() {
    $this->expectException(CiviCRM_API3_Exception::class);
    $priceFieldValues = $this->mockPriceFieldValues();
    $params = [
      'sequential' => 1,
      'schedule' => 'monthly',
    ];
    $selectedPriceFieldValues = [];
    foreach ($priceFieldValues as $priceFieldValue) {
      if (empty($priceFieldValue['membership_type_id'])) {
        $selectedPriceFieldValues[$priceFieldValue['id']] = 10;
      }
      else {
        $selectedPriceFieldValues[$priceFieldValue['id']] = 1;
      }
    }

    $params['price_field_values'] = ['=' => $selectedPriceFieldValues];
    civicrm_api3('PaymentSchedule', 'getByPriceFieldValues', $params);
  }

  public function testGetByPriceFieldValues() {
    $mockNonMembershipPriceFieldInputQuantity = 10;
    $priceFieldValues = $this->mockPriceFieldValues();
    $params = [
      'sequential' => 1,
      'schedule' => 'monthly',
    ];
    $selectedPriceFieldValues = [];
    foreach ($priceFieldValues as $priceFieldValue) {
      if (empty($priceFieldValue['membership_type_id'])) {
        $selectedPriceFieldValues[$priceFieldValue['id']] = $mockNonMembershipPriceFieldInputQuantity;
      }
      else {
        $selectedPriceFieldValues[$priceFieldValue['id']] = 1;
      }
    }
    $params['price_field_values'] = ['IN' => $selectedPriceFieldValues];

    $scheduleInstalments = civicrm_api3('PaymentSchedule', 'getByPriceFieldValues', $params);

    $this->assertEquals(12, $scheduleInstalments['count']);
  }

  /**
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  private function mockPriceFieldValues() {
    $priceFieldValues = [];
    $membershipType = MembershipTypeFabricator::fabricate([
      'name' => 'Rolling Membership Type',
      'period_type' => 'rolling',
      'duration_unit' => 'year',
      'minimum_fee' => 120,
      'duration_interval' => 1,
    ]);

    $priceSetParams = [
      'name' => "test_price_set",
      'extends' => "CiviMember",
      'financial_type_id' => "Member Dues",
      'is_active' => 1,
    ];

    $priceSet = PriceSetFabricator::fabricate($priceSetParams);

    $priceField1 = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Price Field 1",
      'name' => "price_field_1",
      'html_type' => "Radio",
    ]);

    $priceFieldValues[] = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $priceField1['id'],
      'label' => "Price Field Value with Membership Type 1",
      'amount' => 240,
      'membership_type_id' => $membershipType['id'],
      'financial_type_id' => "Member Dues",
    ]);

    $priceField2 = PriceFieldFabricator::fabricate([
      'price_set_id' => $priceSet['id'],
      'label' => "Price Field 1",
      'name' => "price_field_2",
      'html_type' => "Text",
    ]);

    $priceFieldValues[] = PriceFieldValueFabricator::fabricate([
      'price_field_id' => $priceField2['id'],
      'label' => "Price Field Value without Membership Type 1",
      'amount' => 120,
      'financial_type_id' => "Member Dues",
    ]);

    return $priceFieldValues;
  }

}
