
jQuery(document).ready(function($) {
    $(document).on("change", "select#debit_card1", function() {
        var debit_card_val = jQuery(this).val();
        if(debit_card_val != ""){
            jQuery('.debit_card_form').show();
        }else{
            jQuery('.debit_card_form').hide();
        }

    });

    $(document).on("change", "select#select_bank", function() {
    	var emi_val = jQuery(this).val();

    	if(emi_val == '21'){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIIC3">3 Months</option><option value="EMIIC6">6 Months</option><option value="EMIIC9">9 Months</option><option value="EMIIC12">12 Months</option>');
    	}else if( emi_val == 'INDUS' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIIND3">3 Months</option><option value="EMIIND6">6 Months</option><option value="EMIIND9">9 Months</option><option value="EMIIND12">12 Months</option><option value="EMIIND18">18 Months</option><option value="EMIIND24">24 Months</option>');
    	}else if( emi_val == 'HSBC' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIHS03">3 Months</option><option value="EMIHS06">6 Months</option><option value="EMIHS09">9 Months</option><option value="EMIHS12">12 Months</option>');
    	}else if( emi_val == 'KOTAK' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIK3">3 Months</option><option value="EMIK6">6 Months</option><option value="EMIK9">9 Months</option><option value="EMIK12">12 Months</option>');
    	}else if( emi_val == 'AXIS' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIA3">3 Months</option><option value="EMIA6">6 Months</option><option value="EMIA9">9 Months</option><option value="EMIA12">12 Months</option>');
    	}else if( emi_val == '15' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMI3">3 Months</option><option value="EMI6">6 Months</option><option value="EMI9">9 Months</option><option value="EMI12">12 Months</option>');
    	}else if( emi_val == 'ICICID' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIICD3">3 Months</option><option value="EMIICD6">6 Months</option><option value="EMIICD9">9 Months</option><option value="EMIICD12">12 Months</option>');
    	}else if( emi_val == 'SBI' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="SBI03">3 Months</option><option value="SBI06">6 Months</option><option value="SBI09">9 Months</option><option value="SBI12">12 Months</option>');
    	}else if( emi_val == '20' ){
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMI03">3 Months</option><option value="EMI06">6 Months</option><option value="EMI09">9 Months</option><option value="EMI012">12 Months</option><option value="EMI018">18 Months</option><option value="EMI024">24 Months</option>');
    	}else if( emi_val == 'AMEX' ){
      $('select#select_duration').find('option').remove().end().append('<option value="">Select</option><option value="EMIAMEX3">3 Months</option><option value="EMIAMEX6">6 Months</option><option value="EMIAMEX9">9 Months</option><option value="EMAMEX12">12 Months</option>');
     }else{
    		$('select#select_duration').find('option').remove().end().append('<option value="">Select</option>');
    	}
    	$( "select#select_duration" ).click(function() {
    		var emi_dur_val = jQuery(this).val();
    		if( emi_dur_val){
    			$(".emi_card_form").show();
    		}else{
    			$(".emi_card_form").hide();
    		}
    	});
    	$( "select#select_bank" ).click(function() {
    		var emi_dur_val1 = jQuery(this).val();
    		if( emi_dur_val1 == "Select"){
    			$(".emi_card_form").hide();
    		}
    	});
    });
});