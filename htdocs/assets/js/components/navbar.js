import { el } from './dom.js';

export function mountNavbar(root) {
  if (!root) return null;
  const header = el('header', { class: 'navbar' });
  const nav = el('nav', { class: 'navbar__nav', 'aria-label': 'Navigasi utama' });
  const brand = el('a', { class: 'navbar__brand', href: '/' });
  brand.appendChild(document.createTextNode('Ikurin Shop'));
  nav.appendChild(brand);
  const links = el('div', { class: 'navbar__links' });
  const items = [
    { label: 'Beranda', href: '/' },
    { label: 'Katalog', href: '/?category=bakery' },
    { label: 'Keranjang', href: '/cart.html' },
    { label: 'Akun', href: '/profile.html' },
  ];
  for (const item of items) {
    const link = el('a', { class: 'navbar__link', href: item.href }, item.label);
    links.appendChild(link);
  }
  nav.appendChild(links);
  header.appendChild(nav);
  root.appendChild(header);
  return header;
}
