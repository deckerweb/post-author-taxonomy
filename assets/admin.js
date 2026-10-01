/* Local media and progressive clipboard helpers; no remote requests. */
document.addEventListener('click', async (event) => {
  const doc = event.target.closest('[data-pat-document]');
  if (doc) { const dialog = document.getElementById('pat-document-' + doc.dataset.patDocument); if (dialog && dialog.showModal) { event.preventDefault(); dialog.showModal(); } }
  const close = event.target.closest('[data-pat-close]');
  if (close) close.closest('dialog').close();
  const copy = event.target.closest('.pat-copy');
  if (copy) {
    const original = copy.textContent;
    try {
      await navigator.clipboard.writeText(copy.dataset.code);
      copy.textContent = copy.dataset.copied;
      setTimeout(() => { copy.textContent = original; }, 1800);
    } catch (_) {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(copy.previousElementSibling);
      selection.removeAllRanges(); selection.addRange(range);
    }
  }
  const choose = event.target.closest('.pat-choose-photo');
  const remove = event.target.closest('.pat-remove-photo');
  if (!choose && !remove) return;
  const field = (choose || remove).closest('.pat-photo-field');
  const input = field.querySelector('input');
  const preview = field.querySelector('.pat-photo-preview');
  if (remove) { input.value = '0'; preview.replaceChildren(); return; }
  if (!window.wp || !wp.media) return;
  const frame = wp.media({title: PAT_MEDIA.title, button: {text: PAT_MEDIA.button}, library: {type: 'image'}, multiple: false});
  frame.on('select', () => {
    const image = frame.state().get('selection').first().toJSON();
    input.value = String(image.id);
    const img = document.createElement('img');
    img.src = image.sizes?.thumbnail?.url || image.url; img.alt = '';
    preview.replaceChildren(img);
  });
  frame.open();
});

if (window.jQuery) {
  jQuery(document).ajaxSuccess((_event, xhr, settings) => {
    if (typeof settings.data !== 'string' || !/(?:^|&)action=add-tag(?:&|$)/.test(settings.data)) return;
    if (!xhr.responseXML?.querySelector('term')) return;
    const form = document.getElementById('addtag');
    const field = form?.querySelector('.pat-photo-field');
    if (field) { field.querySelector('input').value = '0'; field.querySelector('.pat-photo-preview').replaceChildren(); }
    const website = form?.querySelector('#pat_website'); if (website) website.value = '';
  });
}
