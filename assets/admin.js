(() => {
if (window.patAdminLoaded) return;
window.patAdminLoaded = true;
/* Local media, accessible dialogs and progressive clipboard helpers. */
const patDialogOpeners = new WeakMap();
const patMediaFrames = new WeakMap();

/**
 * Announce a result beside its control without moving keyboard focus.
 * @param {Element} control Button whose containing paragraph receives feedback.
 * @param {string} message Localized feedback text.
 * @returns {void}
 */
function patAnnounce(control, message) {
  let status = control.parentElement.querySelector('.pat-copy-status');
  if (!status) {
    status = document.createElement('span');
    status.className = 'screen-reader-text pat-copy-status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    control.parentElement.append(status);
  }
  status.textContent = message;
}

document.addEventListener('click', async (event) => {
  const copy = event.target.closest('.pat-copy');
  if (copy) {
    const original = copy.textContent;
    try {
      await navigator.clipboard.writeText(copy.dataset.code);
      copy.textContent = copy.dataset.copied;
      patAnnounce(copy, copy.dataset.copied);
      setTimeout(() => { copy.textContent = original; }, 1800);
    } catch (_) {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(copy.previousElementSibling);
      selection.removeAllRanges(); selection.addRange(range);
      patAnnounce(copy, copy.dataset.fallback);
    }
  }
  const choose = event.target.closest('.pat-choose-photo');
  const remove = event.target.closest('.pat-remove-photo');
  if (!choose && !remove) return;
  const field = (choose || remove).closest('.pat-photo-field');
  const input = field.querySelector('input');
  const preview = field.querySelector('.pat-photo-preview');
  const status = field.querySelector('.pat-photo-status');
  const removeButton = field.querySelector('.pat-remove-photo');
  if (remove) {
    input.value = '0'; preview.replaceChildren(); removeButton.disabled = true;
    status.textContent = field.dataset.removed;
    return;
  }
  if (!window.wp || !wp.media) return;
  let frame = patMediaFrames.get(field);
  if (!frame) {
    frame = wp.media({title: PAT_MEDIA.title, button: {text: PAT_MEDIA.button}, library: {type: 'image'}, multiple: false});
    frame.on('select', () => {
      const image = frame.state().get('selection').first().toJSON();
      input.value = String(image.id);
      const img = document.createElement('img');
      img.src = image.sizes?.thumbnail?.url || image.url; img.alt = '';
      preview.replaceChildren(img); removeButton.disabled = false;
      status.textContent = field.dataset.selected;
    });
    frame.on('open', () => {
      const selection = frame.state().get('selection'); selection.reset();
      if (Number(input.value) > 0) {
        const attachment = wp.media.attachment(Number(input.value));
        attachment.fetch(); selection.add(attachment);
      }
    });
    patMediaFrames.set(field, frame);
  }
  frame.open();
});

if (window.jQuery) {
  jQuery(document).ajaxSuccess((_event, xhr, settings) => {
    if (typeof settings.data !== 'string' || !/(?:^|&)action=add-tag(?:&|$)/.test(settings.data)) return;
    if (!xhr.responseXML?.querySelector('term')) return;
    const form = document.getElementById('addtag');
    const field = form?.querySelector('.pat-photo-field');
    if (field) {
      field.querySelector('input').value = '0'; field.querySelector('.pat-photo-preview').replaceChildren();
      field.querySelector('.pat-remove-photo').disabled = true; field.querySelector('.pat-photo-status').textContent = '';
    }
    const website = form?.querySelector('#pat_website'); if (website) website.value = '';
  });
}

/** Guard the single local changelog controller against duplicate scripts and overridden instance methods. */
(() => {
  if (window.patDocumentController || typeof HTMLDialogElement === 'undefined') return;
  const nativeOpen = HTMLDialogElement.prototype.showModal;
  const nativeClose = HTMLDialogElement.prototype.close;
  if (typeof nativeOpen !== 'function' || typeof nativeClose !== 'function') return;
  window.patDocumentController = true;
  for (const dialog of document.querySelectorAll('.pat-document-dialog')) {
    dialog.addEventListener('close', () => {
      dialog.hidden = true;
      const opener = patDialogOpeners.get(dialog);
      if (opener?.isConnected) opener.focus();
    });
    dialog.addEventListener('click', (event) => {
      if (event.target !== dialog) return;
      const box = dialog.getBoundingClientRect();
      if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) nativeClose.call(dialog);
    });
  }
  document.addEventListener('click', (event) => {
    const close = event.target.closest('[data-pat-close]');
    if (close?.closest('.pat-document-dialog')) {
      nativeClose.call(close.closest('dialog'));
      event.preventDefault();
      return;
    }
    const link = event.target.closest('[data-pat-document]');
    if (!link) return;
    const dialog = document.getElementById('pat-document-' + link.dataset.patDocument);
    if (!dialog) return;
    try {
      dialog.hidden = false;
      if (!dialog.open) nativeOpen.call(dialog);
      if (dialog.open) { patDialogOpeners.set(dialog, link); event.preventDefault(); }
      else dialog.hidden = true;
    } catch (_) {
      dialog.hidden = true;
      // Keep the original link navigation when opening fails.
    }
  }, true);
})();

const patSettingsForm = document.querySelector('.pat-settings-form');
if (patSettingsForm) {
  const values = () => JSON.stringify(Array.from(patSettingsForm.querySelectorAll('[name^="pat_settings["]')).map((input) => [input.name, input.type === 'checkbox' ? input.checked : input.value]));
  const original = values();
  patSettingsForm.addEventListener('change', () => {
    patSettingsForm.querySelector('.pat-settings-status').textContent = values() === original ? '' : patSettingsForm.dataset.unsaved;
  });
}

/**
 * Post one scoped read-only admin request and surface localized failures.
 * @param {string} action Registered host AJAX action.
 * @param {Object} values Scalar request fields.
 * @returns {Promise<Object>} Successful response payload.
 */
async function patRequest(action, values) {
  const body = new URLSearchParams({action, nonce: PAT_TOOLS.nonce, ...values});
  const response = await fetch(PAT_TOOLS.url, {method: 'POST', credentials: 'same-origin', body});
  let result;
  try { result = await response.json(); } catch (_) { throw new Error(PAT_TOOLS.error); }
  if (!response.ok || !result.success) throw new Error(result.data?.message || PAT_TOOLS.error);
  return result.data;
}

const patConfig = document.querySelector('.pat-config-form');
if (patConfig && window.PAT_TOOLS) {
  let timer; let generation = 0;
  const status = patConfig.querySelector('.pat-config-status');
  const copyButton = patConfig.querySelector('.pat-copy');
  /** Apply relevant control visibility synchronously before another user action. @returns {void} */
  function updateControls() {
    const single = patConfig.elements.output.value === 'pat-author-box';
    patConfig.elements.author.disabled = !single; patConfig.elements.author.closest('p').hidden = !single;
    patConfig.elements.role.disabled = single; patConfig.elements.role.closest('p').hidden = single;
    patConfig.elements.photo.disabled = patConfig.elements.output.value === 'pat-authors';
    patConfig.elements.website.disabled = patConfig.elements.output.value === 'pat-authors';
    patConfig.elements.photo.closest('label').hidden = patConfig.elements.output.value === 'pat-authors';
    patConfig.elements.website.closest('label').hidden = patConfig.elements.output.value === 'pat-authors';
  }
  /** Render the current controls after one validated request, ignoring obsolete responses. @returns {Promise<void>} */
  async function updatePreview() {
    const request = ++generation;
    const configuration = Object.fromEntries(['output', 'context', 'preview_post', 'author', 'link', 'role'].map((key) => [key, patConfig.elements[key].value]));
    for (const key of ['photo', 'website', 'roles']) configuration[key] = patConfig.elements[key].checked ? 'yes' : 'no';
    if (Number(patConfig.elements.post_override.value) > 0) configuration.preview_post = String(Number(patConfig.elements.post_override.value));
    updateControls();
    status.textContent = PAT_TOOLS.loading; copyButton.disabled = true;
    try {
      const data = await patRequest('pat_preview', {configuration: JSON.stringify(configuration)});
      if (request !== generation) return;
      patConfig.querySelector('.pat-config-code').textContent = data.code;
      patConfig.querySelector('.pat-copy').dataset.code = data.code;
      patConfig.querySelector('.pat-config-preview').innerHTML = data.html;
      status.textContent = data.empty ? PAT_TOOLS.empty : ''; copyButton.disabled = false;
    } catch (error) { if (request === generation) status.textContent = error.message; }
  }
  patConfig.addEventListener('submit', (event) => event.preventDefault());
  patConfig.addEventListener('change', () => { updateControls(); ++generation; copyButton.disabled = true; clearTimeout(timer); timer = setTimeout(updatePreview, 200); });
  patConfig.querySelector('.pat-preview-refresh').addEventListener('click', () => { clearTimeout(timer); updatePreview(); });
  updatePreview();
}

for (const panel of document.querySelectorAll('.pat-attribution')) {
  const list = panel.querySelector('.pat-attribution-list');
  const input = panel.querySelector('.pat-attribution-data');
  const status = panel.querySelector('.pat-attribution-status');
  let dragged;
  /** Update equivalent keyboard controls and optionally serialize the current row order. @param {boolean} persist Whether a user changed the record. @returns {void} */
  function updateRows(persist) {
    const rows = [...list.querySelectorAll('.pat-attribution-row')];
    rows.forEach((row, index) => { row.querySelector('.pat-move-up').disabled = index === 0; row.querySelector('.pat-move-down').disabled = index === rows.length - 1; });
    if (persist) {
      input.value = JSON.stringify(rows.map((row) => ({id: Number(row.dataset.id), role: row.querySelector('.pat-role').value})));
      status.textContent = PAT_TOOLS.changed;
    }
  }
  list.addEventListener('click', (event) => {
    const button = event.target.closest('.pat-move-up, .pat-move-down');
    if (!button) return;
    const row = button.closest('.pat-attribution-row');
    if (button.matches('.pat-move-up') && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
    if (button.matches('.pat-move-down') && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
    updateRows(true);
    if (!button.disabled) button.focus(); else row.querySelector('select').focus();
  });
  list.addEventListener('change', () => updateRows(true));
  list.addEventListener('dragstart', (event) => {
    dragged = event.target.closest('.pat-attribution-row');
    if (!dragged || event.target.matches('select,button')) { event.preventDefault(); return; }
    event.dataTransfer.setData('text/plain', dragged.dataset.id); event.dataTransfer.effectAllowed = 'move'; dragged.classList.add('pat-dragging');
  });
  list.addEventListener('dragover', (event) => { if (dragged) { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; } });
  list.addEventListener('drop', (event) => {
    const target = event.target.closest('.pat-attribution-row');
    if (!dragged || !target || target === dragged) return;
    event.preventDefault();
    const after = event.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
    list.insertBefore(dragged, after ? target.nextElementSibling : target); updateRows(true);
  });
  list.addEventListener('dragend', () => { dragged?.classList.remove('pat-dragging'); dragged = null; });
  panel.querySelector('.pat-refresh-authors').addEventListener('click', async (event) => {
    const button = event.currentTarget; button.disabled = true;
    const existing = [...list.querySelectorAll('.pat-attribution-row')].map((row) => ({id: row.dataset.id, role: row.querySelector('select').value}));
    const values = {post_id: panel.dataset.post};
    const edited = window.wp?.data?.select('core/editor')?.getEditedPostAttribute('pat-author');
    const classic = document.getElementById('tax-input-pat-author');
    if (Array.isArray(edited)) values.ids = JSON.stringify(edited);
    else if (classic) values.names = classic.value;
    try {
      const data = await patRequest('pat_attribution_rows', values);
      list.innerHTML = data.html;
      const available = new Map([...list.querySelectorAll('.pat-attribution-row')].map((row) => [row.dataset.id, row]));
      const ordered = [];
      existing.forEach((item) => { const row = available.get(item.id); if (row) { row.querySelector('select').value = item.role; ordered.push(row); available.delete(item.id); } });
      ordered.push(...available.values());
      if (ordered.length) list.replaceChildren(...ordered);
      updateRows(true); status.textContent = PAT_TOOLS.refreshed;
    } catch (error) { status.textContent = error.message; }
    finally { button.disabled = false; }
  });
  if (window.wp?.data?.subscribe) {
    let saving = false; let submittedValue = '';
    const unsubscribe = wp.data.subscribe(() => {
      const editor = wp.data.select('core/editor');
      if (!editor?.isSavingPost) return;
      const metaBoxes = wp.data.select('core/edit-post');
      const pending = editor.isSavingPost() || Boolean(metaBoxes?.isSavingMetaBoxes?.());
      if (pending && !saving) { saving = true; submittedValue = input.value; }
      if (!pending && saving) {
        if (editor.didPostSaveRequestSucceed?.() && submittedValue === input.value) status.textContent = '';
        saving = false;
      }
    });
    window.addEventListener('pagehide', unsubscribe, {once: true});
  }
  updateRows(false);
}

})();
