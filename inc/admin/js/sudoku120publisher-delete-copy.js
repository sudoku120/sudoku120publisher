document.addEventListener(
	'DOMContentLoaded',
	function () {
		if (typeof ClipboardJS !== 'undefined') {
			new ClipboardJS( '.sudoku120publisher-copy-btn' ).on(
				'success',
				function (e) {
					e.trigger.textContent     = sudoku120publisherL10n.copied;
					setTimeout(
						() => {
							e.trigger.textContent = sudoku120publisherL10n.copy;
						},
						2000
					);
				}
			);
		}
	}
);

function sudoku120publisherconfirmDelete(sudoku_id) {
	if (confirm( sudoku120publisherL10n.confirm_delete )) {
		const page           = sudoku120publisherL10n.page;
		const nonce          = sudoku120publisherL10n.nonce;
		window.location.href =
			'?page=' + page + '&delete=' + sudoku_id + '&nonce=' + nonce;
	}
	return false;
}
