document.addEventListener(
	"DOMContentLoaded",
	function () {
		const customAttrInput = document.querySelector( 'input[name="custom_attr"]' );
		const customRadio     = document.querySelector( 'input[name="sudoku_div_attr"][value="custom"]' );

		if (customAttrInput && customRadio) {
			customAttrInput.addEventListener(
				"focus",
				function () {
					customRadio.checked = true;
				}
			);
		}
	}
);
