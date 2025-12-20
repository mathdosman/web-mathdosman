<?php if (empty($useAdminSidebar)): ?>
	<footer class="mt-4 pt-3 border-top">
		<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 small text-muted">
			<div>&copy; <?php echo date('Y'); ?> MATHDOSMAN</div>
			<div class="d-flex flex-wrap gap-3">
				<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/tentang.php">Tentang</a>
				<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/kontak.php">Kontak</a>
				<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/kebijakan-privasi.php">Kebijakan Privasi</a>
				<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/syarat-ketentuan.php">Syarat &amp; Ketentuan</a>
			</div>
		</div>
	</footer>
<?php endif; ?>

<?php if (!empty($useAdminSidebar)): ?>
			</div>
		</div>
<?php endif; ?>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($use_summernote)): ?>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<?php endif; ?>
<script>
(() => {
	const body = document.body;
	const toggle = document.getElementById('sidebarToggle');
	const backdrop = document.getElementById('sidebarBackdrop');

	if (!toggle) {
		return;
	}

	const syncDefault = () => {
		// Default: open on md+; closed on small screens.
		if (window.matchMedia('(min-width: 768px)').matches) {
			body.classList.remove('sidebar-collapsed');
		} else {
			body.classList.add('sidebar-collapsed');
		}
	};

	const closeSidebar = () => body.classList.add('sidebar-collapsed');
	const toggleSidebar = () => body.classList.toggle('sidebar-collapsed');

	syncDefault();

	toggle.addEventListener('click', toggleSidebar);
	if (backdrop) {
		backdrop.addEventListener('click', closeSidebar);
	}

	window.addEventListener('resize', syncDefault);
})();
</script>

<script>
(() => {
	window.getCsrfToken = () => {
		const meta = document.querySelector('meta[name="csrf-token"]');
		return meta ? (meta.getAttribute('content') || '') : '';
	};
})();
</script>

<script>
(() => {
	if (typeof Swal === 'undefined') {
		return;
	}

	// SweetAlert2 confirmations for destructive actions.
	document.addEventListener('submit', (e) => {
		const form = e.target;
		if (!(form instanceof HTMLFormElement)) {
			return;
		}
		if (!form.hasAttribute('data-swal-confirm')) {
			return;
		}
		// Avoid infinite loop when we re-submit programmatically.
		if (form.dataset.swalConfirmed === '1') {
			return;
		}

		e.preventDefault();
		const title = form.getAttribute('data-swal-title') || 'Konfirmasi';
		const text = form.getAttribute('data-swal-text') || 'Lanjutkan aksi ini?';
		const confirmText = form.getAttribute('data-swal-confirm-text') || 'Ya';
		const cancelText = form.getAttribute('data-swal-cancel-text') || 'Batal';

		Swal.fire({
			title,
			text,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: confirmText,
			cancelButtonText: cancelText,
			reverseButtons: true,
		}).then((res) => {
			if (res.isConfirmed) {
				form.dataset.swalConfirmed = '1';
				form.submit();
			}
		});
	}, true);

	// Convert Bootstrap alerts (danger/success/warning) into SweetAlert2 popups.
	// Keep alert-info for inline informational messages.
	document.addEventListener('DOMContentLoaded', () => {
		const candidates = Array.from(document.querySelectorAll('.alert.alert-danger, .alert.alert-success, .alert.alert-warning'));
		if (candidates.length === 0) {
			return;
		}

		const pick = (cls) => candidates.find((el) => el.classList.contains(cls));
		const alertEl = pick('alert-danger') || pick('alert-success') || pick('alert-warning') || candidates[0];
		if (!alertEl) {
			return;
		}

		let icon = 'info';
		let title = 'Informasi';
		if (alertEl.classList.contains('alert-danger')) {
			icon = 'error';
			title = 'Gagal';
		} else if (alertEl.classList.contains('alert-success')) {
			icon = 'success';
			title = 'Berhasil';
		} else if (alertEl.classList.contains('alert-warning')) {
			icon = 'warning';
			title = 'Perhatian';
		}

		const list = alertEl.querySelector('ul');
		const html = list ? list.outerHTML : (alertEl.innerHTML || '').trim();
		const text = (alertEl.innerText || '').trim();

		// Hide all these alerts to avoid duplicate UI.
		candidates.forEach((el) => {
			el.style.display = 'none';
		});

		Swal.fire({
			title,
			icon,
			html: html !== '' ? html : undefined,
			text: html !== '' ? undefined : text,
		});
	});
})();
</script>
</body>
</html>
