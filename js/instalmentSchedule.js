CRM.$(function ($) {
  $(".schedule-row").on('click', function (e) {
    e.preventDefault();
    $className = 'expanded';
    if ($(this).hasClass($className)) {
      $(this).removeClass($className);
      $(this).closest('tr').next('tr').hide();
    } else {
      $(this).addClass($className);
      $(this).closest('tr').next('tr').show();
    }
  });

  $('.schedule-row-active')
      .on('click', function(e) {

        var sched_table = document.getElementById('instalment_row_table');
        var checks = sched_table.getElementsByClassName('schedule-row-active');
        var payments = CRM.vars['membershipextras_paymentplan'];
        var instalment_amount = 0.0;
        var instalment_tax_amount = 0.0;
        var instalment_total_amount =0.0;
        console.log('Update payment amount');

        //find checked boxes
        for(var element in checks)
        {
           if(!checks[element].checked) continue;
           var ident = checks[element].dataset.ident;
           if(payments[ident-1].hasOwnProperty('instalment_amount'))
               instalment_amount = instalment_amount + payments[ident-1]['instalment_amount'];

           if(payments[ident-1].hasOwnProperty('instalment_tax_amount'))
               instalment_tax_amount = instalment_tax_amount + payments[ident-1]['instalment_tax_amount'];

           if(payments[ident-1].hasOwnProperty('instalment_total_amount'))
               instalment_total_amount = instalment_total_amount + payments[ident-1]['instalment_total_amount'];
        }

        var total_element = document.getElementById('instalment-total-amount');
        total_element.innerText = instalment_total_amount.toFixed(2)
        var instalment_amount_element = document.getElementById('instalment-amount');
        instalment_amount_element.innerText = instalment_amount.toFixed(2)
        var instalment_tax_amount_element = document.getElementById('instalment-tax-amount');
        instalment_tax_amount_element.innerText = instalment_tax_amount.toFixed(2)

          // update form element
          var sched_storage = document.getElementById('payment_plan_datastorage');
          // play back shown data
          sched_storage.value = "{";
          sched_storage.value += '"payments":' +JSON.stringify(payments) + ",";
          sched_storage.value += '"payment_selected":{';

          for(var element in checks) {
              if(element != '0') sched_storage.value += ",";
              if(checks.hasOwnProperty(element))
                  if(checks[element].dataset.hasOwnProperty('ident'))
                  {
                      var ident = checks[element].dataset.ident;
                      sched_storage.value += '"' + ident + '":' + checks[element].checked;
                  }

          }

          sched_storage.value +="}}";
          console.log("Payments " + sched_storage.value );


      });
});
