(() => {
  const form = document.querySelector('form[action*="submit"]');
  if (!form) {
    return;
  }

  const storageKey = 'simapres_student_submit_draft_v1';
  const ignoredInputTypes = new Set(['password', 'file', 'hidden']);

  const serialize = () => {
    const data = {};
    const fields = form.querySelectorAll('input, textarea, select');

    fields.forEach((field) => {
      if (!field.name || field.disabled) {
        return;
      }

      if (ignoredInputTypes.has(field.type)) {
        return;
      }

      if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) {
        return;
      }

      data[field.name] = field.value;
    });

    localStorage.setItem(storageKey, JSON.stringify({
      savedAt: new Date().toISOString(),
      data,
    }));
  };

  const restore = () => {
    const raw = localStorage.getItem(storageKey);
    if (!raw) return;

    try {
      const parsed = JSON.parse(raw);
      const data = parsed?.data || {};

      Object.entries(data).forEach(([name, value]) => {
        const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
        if (!field) return;

        if (field.type === 'checkbox' || field.type === 'radio') {
          field.checked = field.value === value;
          return;
        }

        field.value = value;
      });
    } catch (error) {
      localStorage.removeItem(storageKey);
    }
  };

  const clearDraft = () => {
    localStorage.removeItem(storageKey);
  };

  form.addEventListener('input', serialize);
  form.addEventListener('change', serialize);
  form.addEventListener('submit', clearDraft);

  if (!navigator.onLine) {
    restore();
  } else {
    const hasDraft = localStorage.getItem(storageKey);
    if (hasDraft) restore();
  }
})();
