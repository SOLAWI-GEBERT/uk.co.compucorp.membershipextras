<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id 
 * @property string $contribution_recur_id 
 * @property string $line_item_id 
 * @property string $start_date 
 * @property string $end_date 
 * @property bool|string $auto_renew 
 * @property bool|string $is_removed 
 */
class CRM_MembershipExtras_DAO_ContributionRecurLineItem extends CRM_MembershipExtras_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'membershipextras_subscription_line';

}
