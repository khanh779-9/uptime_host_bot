</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
	(() => {
		const popups = document.querySelectorAll('.floating-popup');
		if (popups.length === 0) {
			return;
		}

		const closePopup = (popup) => {
			if (!popup || popup.classList.contains('is-closing')) {
				return;
			}

			popup.classList.add('is-closing');
			window.setTimeout(() => {
				popup.remove();
			}, 200);
		};

		popups.forEach((popup) => {
			const closeButton = popup.querySelector('.floating-popup-close');
			if (closeButton) {
				closeButton.addEventListener('click', () => closePopup(popup));
			}

			const autoCloseMs = Number.parseInt(popup.dataset.autoclose || '5000', 10);
			if (Number.isFinite(autoCloseMs) && autoCloseMs > 0) {
				window.setTimeout(() => closePopup(popup), autoCloseMs);
			}
		});
	})();
</script>
</body>
</html>
