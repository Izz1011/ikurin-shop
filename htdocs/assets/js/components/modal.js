export function openModal({ title = '', body = null, footer = null } = {}) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');

  const modal = document.createElement('div');
  modal.className = 'modal';

  const header = document.createElement('div');
  header.className = 'modal__header';
  const titleNode = document.createElement('h3');
  titleNode.className = 'modal__title';
  titleNode.textContent = title;
  const closeButton = document.createElement('button');
  closeButton.type = 'button';
  closeButton.className = 'modal__close';
  closeButton.setAttribute('aria-label', 'Tutup');
  closeButton.textContent = '×';
  header.appendChild(titleNode);
  header.appendChild(closeButton);

  const bodyNode = document.createElement('div');
  bodyNode.className = 'modal__body';
  if (typeof body === 'string') {
    bodyNode.textContent = body;
  } else if (body) {
    bodyNode.appendChild(body);
  }

  const footerNode = document.createElement('div');
  footerNode.className = 'modal__footer';
  if (footer) footerNode.appendChild(footer);

  modal.appendChild(header);
  modal.appendChild(bodyNode);
  modal.appendChild(footerNode);
  overlay.appendChild(modal);
  document.body.appendChild(overlay);

  const close = () => {
    overlay.remove();
  };
  closeButton.addEventListener('click', close);
  overlay.addEventListener('click', (event) => {
    if (event.target === overlay) close();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });

  return { close };
}
