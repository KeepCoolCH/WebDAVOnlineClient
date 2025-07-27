document.addEventListener('DOMContentLoaded', () => {
  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('fileInput');
  const basePath = document.getElementById('uploadPath').value;

  dropzone.addEventListener('click', () => fileInput.click());

  dropzone.addEventListener('dragover', e => {
    e.preventDefault();
    dropzone.classList.add('dragover');
  });

  dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
  });

  dropzone.addEventListener('drop', async e => {
    e.preventDefault();
    dropzone.classList.remove('dragover');

    const files = [];
    const items = e.dataTransfer.items;
    const traversePromises = [];

    for (let i = 0; i < items.length; i++) {
      const item = items[i].webkitGetAsEntry?.();
      if (item) {
        traversePromises.push(traverseFileTree(item, '', files));
      }
    }

    await Promise.all(traversePromises);

    if (files.length > 0) {
      uploadFiles(files);
    }
  });

  fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files).map(file => ({
      file,
      path: file.webkitRelativePath || file.name
    }));
    showSpinner();
    uploadFiles(files);
  });

  async function traverseFileTree(entry, path = '', fileList = []) {
    showSpinner();
    return new Promise(resolve => {
      if (entry.isFile) {
        entry.file(file => {
          fileList.push({ file, path: path + file.name });
          resolve();
        });
      } else if (entry.isDirectory) {
        const dirReader = entry.createReader();
        const entries = [];

        const readAllEntries = () => {
          dirReader.readEntries(async batch => {
            if (batch.length === 0) {
              for (const subEntry of entries) {
                await traverseFileTree(subEntry, path + entry.name + '/', fileList);
              }
              resolve();
            } else {
              entries.push(...batch);
              readAllEntries();
            }
          });
        };

        readAllEntries();
      }
    });
  }

  function uploadFiles(files) {
    if (!files.length) return;

    const formData = new FormData();
    files.forEach((entry, index) => {
      formData.append(`file_${index}`, entry.file);
      formData.append(`path_${index}`, entry.path);
    });

    formData.append('count', files.length);
    formData.append('basepath', basePath);

    fetch('inc/handlers/upload.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(text => {
      if (text.startsWith('REDIRECT:')) {
        const url = text.replace('REDIRECT: ', '').trim();
        window.location.href = url;
      }
    })
    .catch(err => {
      console.error(err);
      alert('❌ Upload failed.');
    });
  }
});