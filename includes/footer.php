<?php if (empty($useAdminSidebar) && empty($useStudentSidebar)): ?>
	<?php
		$scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
		$isHomePage = (bool)preg_match('~/index\\.php$~', $scriptName);
	?>
	<footer class="mt-4 pt-3 border-top">
		<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 small text-muted">
			<div>
				<?php if ($isHomePage): ?>
					<a href="<?php echo htmlspecialchars((string)$base_url); ?>/login.php" style="color: inherit; text-decoration: none;">&copy; <?php echo date('Y'); ?> MATHDOSMAN</a>
				<?php else: ?>
					<span>&copy; <?php echo date('Y'); ?> MATHDOSMAN</span>
				<?php endif; ?>
			</div>
			<?php if (empty($hide_public_footer_links)): ?>
				<div class="d-flex flex-wrap gap-3">
					<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/tentang.php">Tentang</a>
					<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/kontak.php">Kontak</a>
					<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/kebijakan-privasi.php">Kebijakan Privasi</a>
					<a class="link-secondary text-decoration-none" href="<?php echo $base_url; ?>/syarat-ketentuan.php">Syarat &amp; Ketentuan</a>
				</div>
			<?php endif; ?>
		</div>
	</footer>
<?php endif; ?>

<?php if (!empty($useAdminSidebar) || !empty($useStudentSidebar)): ?>
			</div>
		</div>
<?php endif; ?>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($useAdminSidebar)): ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
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

<?php if (!empty($useAdminSidebar)): ?>
<script>
(() => {
	// Keep sidebar scroll position and active item between page loads.
	const sidebar = document.getElementById('adminSidebar');
	if (!sidebar) return;

	const path = String(window.location.pathname || '');
	let cut = path.indexOf('/siswa/admin/');
	if (cut < 0) cut = path.indexOf('/admin/');
	const base = (cut >= 0) ? path.slice(0, cut) : '';
	const scrollKey = 'md_admin_sidebar_scroll:' + base;
	const activeKey = 'md_admin_sidebar_active:' + base;

	// Restore scroll position early.
	try {
		const saved = sessionStorage.getItem(scrollKey);
		if (saved !== null) {
			const n = parseInt(saved, 10);
			if (!Number.isNaN(n)) {
				sidebar.scrollTop = n;
			}
		}
	} catch (e) {}

	// Persist scroll position.
	let scrollTimer = 0;
	const saveScroll = () => {
		try { sessionStorage.setItem(scrollKey, String(sidebar.scrollTop || 0)); } catch (e) {}
	};
	sidebar.addEventListener('scroll', () => {
		if (scrollTimer) return;
		scrollTimer = window.setTimeout(() => {
			scrollTimer = 0;
			saveScroll();
		}, 120);
	}, { passive: true });

	// Ensure we save scroll before navigating via sidebar clicks.
	sidebar.addEventListener('click', (e) => {
		const a = e.target && (e.target.closest ? e.target.closest('a') : null);
		if (!a) return;
		if (!(a instanceof HTMLAnchorElement)) return;
		// Skip if link opens in new tab/window.
		if (a.target && a.target !== '_self') return;
		saveScroll();
		try { sessionStorage.setItem(activeKey, a.href || ''); } catch (err) {}
	});

	// Apply active styling based on current URL (fallback if PHP-side detection misses a page).
	try {
		const currentPath = new URL(window.location.href).pathname;
		const links = Array.from(sidebar.querySelectorAll('a.sidebar-link'));
		let matched = null;
		for (const link of links) {
			try {
				const linkPath = new URL(link.href, window.location.href).pathname;
				if (linkPath === currentPath) {
					matched = link;
					break;
				}
			} catch (e) {}
		}

		if (!matched) {
			// Optional fallback: last clicked sidebar link.
			const last = sessionStorage.getItem(activeKey) || '';
			if (last) {
				for (const link of links) {
					if (String(link.href || '') === String(last)) {
						matched = link;
						break;
					}
				}
			}
		}

		if (matched) {
			for (const link of links) {
				link.classList.toggle('active', link === matched);
				if (link === matched) {
					link.setAttribute('aria-current', 'page');
				} else {
					if (link.getAttribute('aria-current') === 'page') {
						link.removeAttribute('aria-current');
					}
				}
			}
		}
	} catch (e) {}
})();
</script>
<?php endif; ?>

<?php if (empty($useAdminSidebar) && !empty($useStudentSidebar)): ?>
<script>
(() => {
	// Keep sidebar scroll position and active item between page loads (student).
	const sidebar = document.getElementById('studentSidebar');
	if (!sidebar) return;

	const path = String(window.location.pathname || '');
	let cut = path.indexOf('/siswa/');
	const base = (cut >= 0) ? path.slice(0, cut) : '';
	const scrollKey = 'md_student_sidebar_scroll:' + base;
	const activeKey = 'md_student_sidebar_active:' + base;

	try {
		const saved = sessionStorage.getItem(scrollKey);
		if (saved !== null) {
			const n = parseInt(saved, 10);
			if (!Number.isNaN(n)) sidebar.scrollTop = n;
		}
	} catch (e) {}

	let scrollTimer = 0;
	const saveScroll = () => {
		try { sessionStorage.setItem(scrollKey, String(sidebar.scrollTop || 0)); } catch (e) {}
	};
	sidebar.addEventListener('scroll', () => {
		if (scrollTimer) return;
		scrollTimer = window.setTimeout(() => {
			scrollTimer = 0;
			saveScroll();
		}, 120);
	}, { passive: true });

	sidebar.addEventListener('click', (e) => {
		const a = e.target && (e.target.closest ? e.target.closest('a') : null);
		if (!a) return;
		if (!(a instanceof HTMLAnchorElement)) return;
		if (a.target && a.target !== '_self') return;
		saveScroll();
		try { sessionStorage.setItem(activeKey, a.href || ''); } catch (err) {}
	});

	try {
		const currentPath = new URL(window.location.href).pathname;
		const links = Array.from(sidebar.querySelectorAll('a.sidebar-link'));
		let matched = null;
		for (const link of links) {
			try {
				const linkPath = new URL(link.href, window.location.href).pathname;
				if (linkPath === currentPath) { matched = link; break; }
			} catch (e) {}
		}
		if (!matched) {
			const last = sessionStorage.getItem(activeKey) || '';
			if (last) {
				for (const link of links) {
					if (String(link.href || '') === String(last)) { matched = link; break; }
				}
			}
		}
		if (matched) {
			for (const link of links) {
				link.classList.toggle('active', link === matched);
				if (link === matched) link.setAttribute('aria-current', 'page');
				else if (link.getAttribute('aria-current') === 'page') link.removeAttribute('aria-current');
			}
		}
	} catch (e) {}
})();
</script>
<?php endif; ?>

<script>
(() => {
	window.getCsrfToken = () => {
		const meta = document.querySelector('meta[name="csrf-token"]');
		return meta ? (meta.getAttribute('content') || '') : '';
	};
})();
</script>

<?php if (!empty($useAdminSidebar)): ?>
<script>
(() => {
	if (typeof tinymce === 'undefined') {
		return;
	}

	const baseSelector = 'textarea:not(.no-tinymce):not([data-editor="plain"]):not([disabled])';
	const hasAnyTextarea = document.querySelector(baseSelector);
	if (!hasAnyTextarea) {
		return;
	}

	// Use a relative URL so it automatically matches the current scheme (http/https)
	// and avoids Mixed Content issues behind reverse proxies.
	const uploadUrl = 'uploadeditor.php';

	const triggerSaveSafe = () => {
		try {
			if (typeof tinymce !== 'undefined') {
				tinymce.triggerSave();
			}
		} catch (e) {}
	};

	// Ensure editor content is synced into underlying <textarea> before any submit handlers/validation.
	document.addEventListener('submit', () => {
		triggerSaveSafe();
	}, true);

	const commonConfig = {
		menubar: false,
		statusbar: false,
		branding: false,
		promotion: false,
		convert_urls: false,
		relative_urls: false,
		remove_script_host: false,
		plugins: 'lists link image table code autoresize',
		toolbar: 'undo redo | bold italic underline | bullist numlist | link image table | code',
		table_default_attributes: {
			border: '1',
		},
		images_upload_handler: (blobInfo, progress) => {
			return new Promise((resolve, reject) => {
				try {
					const xhr = new XMLHttpRequest();
					xhr.open('POST', uploadUrl);
					xhr.responseType = 'json';

					const token = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken() : '';
					if (token) {
						xhr.setRequestHeader('X-CSRF-Token', token);
					}

					xhr.upload.onprogress = (e) => {
						if (e.lengthComputable && typeof progress === 'function') {
							progress((e.loaded / e.total) * 100);
						}
					};

					xhr.onerror = () => reject('Upload gagal.');
					xhr.onload = () => {
						if (xhr.status < 200 || xhr.status >= 300) {
							const msg = (xhr.response && xhr.response.error) ? xhr.response.error : ('HTTP ' + xhr.status);
							reject(msg);
							return;
						}

						const res = xhr.response;
						if (res && typeof res.url === 'string' && res.url) {
							resolve(res.url);
							return;
						}
						reject('Respon upload tidak valid.');
					};

					const formData = new FormData();
					formData.append('file', blobInfo.blob(), blobInfo.filename());
					xhr.send(formData);
				} catch (e) {
					reject('Upload gagal.');
				}
			});
		},
		setup: (editor) => {
			// Keep textarea updated during typing (helps required validation + debug tools).
			editor.on('change keyup setcontent', () => {
				triggerSaveSafe();
			});
		},
	};

	document.addEventListener('DOMContentLoaded', () => {
		try {
			// 1) Pertanyaan: height 320
			const pertanyaan = document.querySelector('textarea#pertanyaan');
			if (pertanyaan && pertanyaan.matches(baseSelector)) {
				tinymce.init({
					...commonConfig,
					selector: 'textarea#pertanyaan',
					height: 320,
					min_height: 320,
				});
			}

			// 2) Others keep default height 280
			tinymce.init({
				...commonConfig,
				selector: baseSelector + ':not(#pertanyaan)',
				height: 280,
			});
		} catch (e) {
			// no-op
		}
	});
})();
</script>
<?php endif; ?>

<script>
(() => {
	// Optional debugging helper for form submits.
	// Enable by adding: ?md_debug_submit=1 (or &md_debug_submit=1) to the URL.
	try {
		const params = new URLSearchParams(window.location.search || '');
		const enabled = params.get('md_debug_submit') === '1' || params.get('md_debug') === '1';
		window.__md_debug_submit = !!enabled;
		if (!enabled) return;

		const safeStr = (v) => {
			try {
				if (v === null || typeof v === 'undefined') return '';
				return String(v);
			} catch (e) {
				return '';
			}
		};

		const dumpForm = (form) => {
			if (!(form instanceof HTMLFormElement)) return;
			const id = form.getAttribute('id') || '(no-id)';
			const action = form.getAttribute('action') || window.location.href;
			const method = (form.getAttribute('method') || 'GET').toUpperCase();
			console.groupCollapsed('[MATHDOSMAN DEBUG] submit', { id, method, action });

			try {
				const els = Array.from(form.elements || []);
				const disabled = els
					.filter((el) => el && (el.disabled === true) && el.name)
					.map((el) => ({ name: el.name, id: el.id || '', type: el.type || el.tagName }));
				if (disabled.length) {
					console.warn('[MATHDOSMAN DEBUG] disabled controls (not submitted):', disabled);
				}
			} catch (e) {}

			// What browser will actually submit
			try {
				const fd = new FormData(form);
				const keys = [];
				fd.forEach((_, k) => { keys.push(k); });
				console.log('[MATHDOSMAN DEBUG] formdata keys:', Array.from(new Set(keys)));
				const peek = (k) => {
					try {
						const all = fd.getAll(k);
						if (!all || !all.length) return null;
						return all.map((x) => {
							if (x instanceof File) return { file: x.name, size: x.size, type: x.type };
							const s = safeStr(x);
							return s.length > 120 ? (s.slice(0, 120) + '…') : s;
						});
					} catch (e) {
						return null;
					}
				};
				console.log('[MATHDOSMAN DEBUG] pertanyaan:', peek('pertanyaan'));
				console.log('[MATHDOSMAN DEBUG] pilihan_1:', peek('pilihan_1'));
				console.log('[MATHDOSMAN DEBUG] pilihan_2:', peek('pilihan_2'));
				console.log('[MATHDOSMAN DEBUG] pilihan_3:', peek('pilihan_3'));
				console.log('[MATHDOSMAN DEBUG] pilihan_4:', peek('pilihan_4'));
				console.log('[MATHDOSMAN DEBUG] pilihan_5:', peek('pilihan_5'));
				console.log('[MATHDOSMAN DEBUG] jawaban_benar[]:', peek('jawaban_benar[]'));
				console.log('[MATHDOSMAN DEBUG] csrf_token:', peek('csrf_token'));
			} catch (e) {
				console.error('[MATHDOSMAN DEBUG] FormData error:', e);
			}

			console.groupEnd();
		};

		window.mdDumpForm = (formId = 'questionForm') => {
			try {
				const form = document.getElementById(formId);
				dumpForm(form);
			} catch (e) {}
		};

		console.info('[MATHDOSMAN DEBUG] Submit debug enabled. Use mdDumpForm() to dump current form state.');
		document.addEventListener('submit', (e) => {
			try { dumpForm(e.target); } catch (err) {}
		}, true);

		// Some browsers/flows (native validation, custom handlers) can block submit events.
		// Dump on submit button clicks too, so we still see what would be submitted.
		document.addEventListener('click', (e) => {
			try {
				const t = e.target;
				if (!(t instanceof Element)) return;
				const btn = t.closest('button, input');
				if (!btn) return;
				// Use the DOM property (defaults to "submit" for <button> without type attribute).
				let type = '';
				try {
					// HTMLButtonElement / HTMLInputElement both have .type
					// @ts-ignore
					type = String(btn.type || '').toLowerCase();
				} catch (e) {
					type = String((btn.getAttribute('type') || '')).toLowerCase();
				}
				if (type !== 'submit') return;
				const form = btn.closest('form');
				if (!form) return;
				dumpForm(form);
			} catch (err) {}
		}, true);
	} catch (e) {
		// no-op
	}
})();
</script>

<script>
(() => {
	// Auto-toggle button text for Bootstrap collapse triggers.
	// Usage: add data-md-toggle-closed="Buka" and data-md-toggle-open="Tutup" on the button.
	const buttons = document.querySelectorAll('[data-md-toggle-closed][data-md-toggle-open][data-bs-toggle="collapse"]');
	if (!buttons.length) return;

	const setText = (btn, expanded) => {
		try {
			const t = expanded ? (btn.getAttribute('data-md-toggle-open') || '') : (btn.getAttribute('data-md-toggle-closed') || '');
			if (t) btn.textContent = t;
		} catch (e) {}
	};

	buttons.forEach((btn) => {
		try {
			const targetSel = btn.getAttribute('data-bs-target') || btn.getAttribute('href') || '';
			if (!targetSel) return;
			const target = document.querySelector(targetSel);
			if (!target) return;

			// Initial state from aria-expanded
			const expanded = (btn.getAttribute('aria-expanded') === 'true');
			setText(btn, expanded);

			target.addEventListener('shown.bs.collapse', () => setText(btn, true));
			target.addEventListener('hidden.bs.collapse', () => setText(btn, false));
		} catch (e) {}
	});
})();
</script>

<script>
(() => {
	// Automatically attach CSRF token to all POST forms.
	document.addEventListener('DOMContentLoaded', () => {
		const token = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken() : '';
		if (!token) return;
		const forms = Array.from(document.querySelectorAll('form[method="post"], form[method="POST"]'));
		forms.forEach((form) => {
			if (!(form instanceof HTMLFormElement)) return;
			if (form.querySelector('input[name="csrf_token"]')) return;
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'csrf_token';
			input.value = token;
			form.appendChild(input);
		});
	});
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

		// Allow confirmation to be configured either on the form or on the submit button.
		const submitter = e.submitter || document.activeElement;
		const hasOnSubmitter = submitter && submitter instanceof HTMLElement && submitter.hasAttribute('data-swal-confirm');
		const hasOnForm = form.hasAttribute('data-swal-confirm');
		if (!hasOnForm && !hasOnSubmitter) {
			return;
		}
		// Avoid infinite loop when we re-submit programmatically.
		if (form.dataset.swalConfirmed === '1') {
			return;
		}

		e.preventDefault();
		const src = hasOnSubmitter ? submitter : form;
		const title = src.getAttribute('data-swal-title') || 'Konfirmasi';
		const text = src.getAttribute('data-swal-text') || 'Lanjutkan aksi ini?';
		const confirmText = src.getAttribute('data-swal-confirm-text') || 'Ya';
		const cancelText = src.getAttribute('data-swal-cancel-text') || 'Batal';

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
				// Prefer requestSubmit(submitter) to preserve which button was clicked (important for name/value like action=mark_done).
				if (typeof form.requestSubmit === 'function') {
					try {
						if (submitter && (submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement)) {
							form.requestSubmit(submitter);
						} else {
							form.requestSubmit();
						}
						return;
					} catch (err) {
						// Fallback below.
					}
				}

				// Fallback for older browsers: ensure submitter name/value is present by appending a hidden input.
				try {
					if (submitter && submitter instanceof HTMLElement) {
						const n = submitter.getAttribute('name') || '';
						if (n) {
							const v = submitter.getAttribute('value') || '';
							const hidden = document.createElement('input');
							hidden.type = 'hidden';
							hidden.name = n;
							hidden.value = v;
							form.appendChild(hidden);
						}
					}
				} catch (e) {}
				form.submit();
			}
		});
	}, true);

	// Convert Bootstrap alerts into SweetAlert2 popups.
	// Keep complex/interactive inline callouts (forms/buttons/links) in place.
	document.addEventListener('DOMContentLoaded', () => {
		const selector = '.alert.alert-danger, .alert.alert-success, .alert.alert-warning';
		const candidates = Array.from(document.querySelectorAll(selector)).filter((el) => {
			// Don't convert alerts that contain interactive elements.
			if (el.querySelector('form, button, a, .btn, input, select, textarea')) return false;
			return true;
		});
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

<script>
(() => {
	if (typeof Swal === 'undefined') return;
	const body = document.body;
	if (!body || !body.classList || !body.classList.contains('student-area')) return;

	const params = new URLSearchParams(window.location.search || '');
	const flash = String(params.get('flash') || '').trim();
	if (!flash) return;

	const messages = {
		login_success: { icon: 'success', title: 'Berhasil', text: 'Login berhasil.' },
		logout_success: { icon: 'success', title: 'Berhasil', text: 'Logout berhasil.' },
		login_required: { icon: 'info', title: 'Perlu Login', text: 'Silakan login dulu untuk melanjutkan.' },

		saved: { icon: 'success', title: 'Tersimpan', text: 'Jawaban berhasil disimpan.' },
			done: { icon: 'success', title: 'Dikumpulkan', text: 'Tugas/ujian berhasil dikumpulkan. Nilai dan jawaban ditampilkan.' },
			already_done: { icon: 'info', title: 'Selesai', text: 'Tugas/ujian sudah dikumpulkan. Nilai dan jawaban ditampilkan.' },
		started: { icon: 'success', title: 'Ujian Dimulai', text: 'Ujian dimulai. Timer sudah berjalan.' },
		reopened: { icon: 'success', title: 'Diubah', text: 'Status selesai dibatalkan. Kamu bisa mengerjakan lagi.' },
	};

	const msg = messages[flash];
	if (!msg) {
		// Unknown flash codes are ignored on purpose.
		return;
	}

	// Remove flash from URL to prevent showing again on refresh.
	try {
		params.delete('flash');
		const qs = params.toString();
		const nextUrl = window.location.pathname + (qs ? ('?' + qs) : '') + (window.location.hash || '');
		window.history.replaceState({}, '', nextUrl);
	} catch (e) {}

	Swal.fire({
		icon: msg.icon,
		title: msg.title,
		text: msg.text,
	});
})();
</script>
</body>
</html>
