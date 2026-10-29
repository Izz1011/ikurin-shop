import { el } from './dom.js';
import { formatRupiah } from '../format.js';

export function createProductCard(product) {
  const card = el('article', { class: 'product-card card' });
  const image = el('div', { class: 'product-card__image' });
  const img = el('img', {
    src: product.image_url || '',
    alt: product.product_name,
    loading: 'lazy',
    width: 300,
    height: 300,
  });
  image.appendChild(img);
  const body = el('div', { class: 'product-card__body' });
  const name = el('h3', { class: 'product-card__name' }, product.product_name);
  const price = el('p', { class: 'product-card__price' }, formatRupiah(product.price));
  const store = el('p', { class: 'product-card__store' }, product.store?.store_name || '');
  body.appendChild(name);
  body.appendChild(price);
  body.appendChild(store);
  card.appendChild(image);
  card.appendChild(body);
  return card;
}
