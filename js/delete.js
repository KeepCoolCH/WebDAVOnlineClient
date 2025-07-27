function deletePromptMulti(button) {
  const form = document.getElementById('multi-action-form');
  const selected = form.querySelectorAll('input[name="selected[]"]:checked');
  if (!selected.length) {
    alert("❌ Please select at least one item to delete.");
    return false;
  }
  return confirm('Are you sure you want to delete the selected items?');
}