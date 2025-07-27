function renamePrompt(form) {
  const source = decodeURIComponent(form.source.value);
  const currentName = source.split('/').filter(Boolean).pop();
  const newName = prompt("New name:", currentName);

  if (newName === null || newName.trim() === '' || newName === currentName) return false;

  const target = source.replace(/[^\/]+\/?$/, '') + newName;

  form.target.value = target;
  return true;
}
