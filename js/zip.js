function startZip() {
  const form = document.getElementById('multi-action-form');
  const selected = form.querySelectorAll('input[name="selected[]"]:checked');

  if (!selected.length) {
    alert("❌ Please select at least one item to zip.");
    return false;
  }

  const confirmed = confirm('Create ZIP from selected?');
  if (!confirmed) return false;

  showSpinner();
  return true;
}

async function startZipDownload() {
	const form = document.getElementById('multi-action-form');
	const selected = form.querySelectorAll('input[name="selected[]"]:checked');

	if (!selected.length) {
		alert("❌ Please select at least one item to Download.");
		return false;
	}
	
	const confirmed = confirm('Are you sure you want to download the selected items as ZIP?');
	if (!confirmed) return false;

	showSpinner();
	const formData = new FormData(form);
	formData.append('zip_only', '1');
	
	try {
		const res = await fetch('webdav.php?action=zipdownload', {
			method: 'POST',
			body: formData
		});
		
		const response = await res.json();
		hideSpinner();

		if (response.success && response.zipUrl) {
			window.location.href = response.zipUrl;
		} else {
			alert("❌ ZIP creation failed.");
		}
	} catch (err) {
		hideSpinner();
		alert("❌ ZIP creation failed.");
		console.error(err);
	}
}

async function startZipDownloadDirect(formElement) {
	event?.preventDefault();
	const formData = new FormData(formElement);
	formData.append('zip_only', '1');

	showSpinner();

	try {
		const res = await fetch('webdav.php?action=zipdownload', {
			method: 'POST',
			body: formData
		});
		
		const response = await res.json();
		hideSpinner();

		if (response.success && response.zipUrl) {
			window.location.href = response.zipUrl;
		} else {
			alert("❌ ZIP creation failed.");
		}
	} catch (err) {
		hideSpinner();
		alert("❌ ZIP creation failed.");
		console.error(err);
	}

	return false;
}