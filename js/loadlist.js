document.addEventListener('DOMContentLoaded', () => {
  const selectAll = document.getElementById('select-all');
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      const checkboxes = document.querySelectorAll('input[name="selected[]"]');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });
  }
});

function loadList() {
  fetch("webdav.php?action=list")
    .then(res => res.text())
    .then(xml => {
      document.getElementById("filelist").innerText = xml;
    });
}

