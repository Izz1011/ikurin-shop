// Mock fixtures — data contoh mengikuti bentuk kontrak (FE-002).

export const mockConfig = {
  site_name: 'Ikurin Shop',
  service_fee_percent: 2,
  pusher_key: 'mock-pusher-key',
  pusher_cluster: 'ap1',
  categories: ['bakery', 'snack', 'makanan_berat', 'minuman'],
};

export const mockStores = [
  {
    id: 1,
    store_name: 'Roti Ibu Ani',
    slug: 'roti-ibu-ani',
    category: 'bakery',
    logo_image: null,
    is_open: true,
    address_short: 'Kec. Banjarnegara',
    product_count: 2,
  },
];

export const mockProducts = [
  {
    id: 10,
    product_name: 'Roti Sobek',
    price: 25000,
    stock: 8,
    image_url: null,
    store: { id: 1, store_name: 'Roti Ibu Ani' },
  },
  {
    id: 11,
    product_name: 'Croissant Butter',
    price: 18000,
    stock: 5,
    image_url: null,
    store: { id: 1, store_name: 'Roti Ibu Ani' },
  },
];
