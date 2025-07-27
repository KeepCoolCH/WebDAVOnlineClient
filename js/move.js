function movePrompt(form) {
  const sourceInput = form.querySelector('input[name="source"]');
  const targetInput = form.querySelector('input[name="target"]');
  const source = decodeURIComponent(sourceInput.value);
  const isDir = source.endsWith('/');
  const currentName = source.split('/').filter(Boolean).pop();

  let newTarget = prompt("Move to where? (Enter target path)", source.substring(0, source.lastIndexOf('/') + 1));
  if (newTarget === null || newTarget.trim() === '') return false;

  if (isDir && !newTarget.endsWith('/')) {
    newTarget += '/';
  }

  targetInput.value = newTarget;
  return true;
}

function movePromptMulti(button) {
  const formId = button.getAttribute("form");
  const form = document.getElementById(formId);
  if (!form) {
    alert("❌ Form not found.");
    return false;
  }

  const selected = form.querySelectorAll('input[name="selected[]"]:checked');
  if (!selected.length) {
    alert("❌ Please select at least one item to move.");
    return false;
  }

  const source = decodeURIComponent(selected[0].value);
  let newTarget = prompt("Move to where? (Enter target path)", source.substring(0, source.lastIndexOf('/') + 1));
  if (newTarget === null || newTarget.trim() === "") return false;

  if (source.endsWith('/') && !newTarget.endsWith('/')) {
    newTarget += '/';
  }

  let targetInput = form.querySelector('input[name="target"]');
  if (!targetInput) {
    targetInput = document.createElement("input");
    targetInput.type = "hidden";
    targetInput.name = "target";
    form.appendChild(targetInput);
  }

  targetInput.value = newTarget;
  return true;
}
