let disabled = true;
document.getElementById("conditions_to_approve[terms-and-conditions]").onchange = function() {
	document.getElementById("sslczPayBtn").disabled = !disabled;
	disabled = !disabled;
}
