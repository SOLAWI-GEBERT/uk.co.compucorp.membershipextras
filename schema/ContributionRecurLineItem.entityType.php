<?php
use CRM_MembershipExtras_ExtensionUtil as E;
return [
  'name' => 'ContributionRecurLineItem',
  'table' => 'membershipextras_subscription_line',
  'class' => 'CRM_MembershipExtras_DAO_ContributionRecurLineItem',
  'getInfo' => fn() => [
    'title' => E::ts('Contribution Recur Line Item'),
    'title_plural' => E::ts('Contribution Recur Line Items'),
    'description' => E::ts('Implements a relationship between recurring contributions and line items, used to store the current values for pending installments.'),
    'log' => TRUE,
    'add' => '5.0',
  ],
  'getIndices' => fn() => [
    'index_contribrecurid_lineitemid' => [
      'fields' => [
        'contribution_recur_id' => TRUE,
        'line_item_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Discount Item ID'),
      'add' => '5.0',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contribution_recur_id' => [
      'title' => E::ts('Contribution Recur ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('ID of the recurring contribution.'),
      'add' => '5.0',
      'entity_reference' => [
        'entity' => 'ContributionRecur',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'line_item_id' => [
      'title' => E::ts('Line Item ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('ID of the line item related to the recurring contribution.'),
      'add' => '5.0',
      'entity_reference' => [
        'entity' => 'LineItem',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'start_date' => [
      'title' => E::ts('Start Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('Start date of the period for the membership/recurring contribution.'),
      'add' => '5.0',
    ],
    'end_date' => [
      'title' => E::ts('End Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('End date of the period for the membership/recurring contribution.'),
      'add' => '5.0',
    ],
    'auto_renew' => [
      'title' => E::ts('Auto Renew'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('If the line-item should be auto-renewed or not.'),
      'add' => '5.0',
      'default' => TRUE,
    ],
    'is_removed' => [
      'title' => E::ts('Is Removed'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('If the line-item has been marked as removed or not.'),
      'add' => '5.0',
      'default' => TRUE,
    ],
  ],
];
