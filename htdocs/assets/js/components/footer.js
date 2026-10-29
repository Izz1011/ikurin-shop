import { el } from './dom.js';

export function mountFooter(root) {
  if (!root) return null;
  const footer = el('footer', { class: 'footer' });
  const text = el('p', { class: 'footer__text' }, '© 2026 Ikurin Shop — Marketplace kuliner lokal Banjarnegara.');
  footer.appendChild(text);
  root.appendChild(footer);
  return footer;
}
