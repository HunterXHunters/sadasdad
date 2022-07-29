$(function(){
	$.validator.addMethod("decimal", function(value, element) {
		return /^\d{0,17}(\.\d{2})?$/.test(value);
	}, "Please enter a valid value (eg: 99.78)");

	$.validator.addClassRules("decimal", {
		decimal: true
	});

	$.validator.addMethod("requiredDropdown", function(value, element) {
		return value != '';
	}, "Please select a value");

	$.validator.addClassRules("requiredSelect", {
		requiredDropdown: true
	});

	$.validator.addClassRules("mobile_no", {
		 digits: true,
		 maxlength:10,
		 minlength:10
	});
});