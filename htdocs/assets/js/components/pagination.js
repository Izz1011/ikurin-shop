import { el } from './dom.js';

export function renderPagination({ page = 1, totalPages = 1, onChange } = {}) {
  const nav = el('nav', { class: 'pagination', 'aria-label': 'Navigasi halaman' });
  const list = el('ul', { class: 'pagination__list' });
  nav.appendChild(list);

  const lastPage = Math.max(1, Number(totalPages) || 1);
  const currentPage = Math.min(Math.max(1, Number(page) || 1), lastPage);

  const buildItem = (label, pageValue, { disabled = false, current = false } = {}) => {
    const li = el('li', { class: `pagination__item${disabled ? ' pagination__item--disabled' : ''}` });
    const link = el(
      'a',
      { class: 'pagination__link', href: disabled ? '#' : `#page=${pageValue}` },
      label
    );
    link.setAttribute('data-page', String(pageValue));
    if (disabled) link.setAttribute('aria-disabled', 'true');
    if (current) link.setAttribute('aria-current', 'page');
    li.appendChild(link);
    return li;
  };

  list.appendChild(buildItem('«', Math.max(1, currentPage - 1), { disabled: currentPage <= 1 }));

  const pages = [];
  const totalPagesToShow = 5;
  const startPage = Math.max(1, Math.min(currentPage - 2, lastPage - totalPagesToShow + 1));
  const endPage = Math.min(lastPage, startPage + totalPagesToShow - 1);
  for (let p = startPage; p <= endPage; p += 1) pages.push(p);
  if (endPage < lastPage) pages.push(lastPage);

  for (const p of pages) list.appendChild(buildItem(String(p), p, { current: p === currentPage }));

  list.appendChild(buildItem('»', Math.min(lastPage, currentPage + 1), { disabled: currentPage >= lastPage }));

  nav.addEventListener('click', (event) => {
    const link = event.target.closest('.pagination__link');
    if (!link) return;
    const pageValue = Number(link.getAttribute('data-page'));
    if (onChange && pageValue > 0 && pageValue <= lastPage) onChange(pageValue);
  });

  return nav;
}
