let disabled = true;
let tac_fld = document.getElementById("conditions_to_approve[terms-and-conditions]");
if (tac_fld) {
	tac_fld.onchange = function() {
		let sslpbtn = document.getElementById("sslczPayBtn")
		if (sslpbtn) {
			sslpbtn.disabled = !disabled;
			disabled = !disabled;
		}
	}
}
