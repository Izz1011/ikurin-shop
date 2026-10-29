import { el } from './dom.js';

const items = [
  { label: 'Beranda', href: '/', key: 'home' },
  { label: 'Keranjang', href: '/cart.html', key: 'cart' },
  { label: 'Chat', href: '/chat.html', key: 'chat' },
  { label: 'Pesanan', href: '/orders.html', key: 'orders' },
  { label: 'Akun', href: '/profile.html', key: 'account' },
];

export function mountBottomNav(root) {
  if (!root) return null;
  const nav = el('nav', { class: 'bottom-nav', 'aria-label': 'Navigasi utama' });
  const list = el('ul', { class: 'bottom-nav__list' });
  for (const item of items) {
    const li = el('li', { class: 'bottom-nav__item' });
    const link = el('a', { class: 'bottom-nav__link', href: item.href }, item.label);
    link.setAttribute('data-nav-key', item.key);
    li.appendChild(link);
    list.appendChild(li);
  }
  nav.appendChild(list);
  root.appendChild(nav);
  return nav;
}
