// Mock API handler — mengikuti bentuk kontrak persis (FE-002).
import { mockConfig, mockProducts } from './fixtures.js';

const LATENCY_MIN = 200;
const LATENCY_MAX = 500;

function latency() {
  return LATENCY_MIN + Math.floor(Math.random() * (LATENCY_MAX - LATENCY_MIN));
}

function ok(data, meta = null) {
  return { ok: true, data, meta };
}

function fail(code, message, status, fields = null) {
  return { ok: false, error: { code, message, ...(fields ? { fields } : {}) }, status };
}

function productById(id) {
  const product = mockProducts.find((item) => String(item.id) === String(id));
  if (!product) {
    return null;
  }
  return { ...product, description: 'Mock description.' };
}

function fieldsFrom(body) {
  if (body && typeof body.get === 'function') {
    const fields = {};
    body.forEach((value, key) => {
      fields[key] = value;
    });
    return fields;
  }
  return body || {};
}

let mockSessionUser = null;
let mockStoreApplication = null;

export async function mockRequest(path, { method = 'GET', body = null } = {}) {
  await new Promise((resolve) => setTimeout(resolve, latency()));

  const [pathname, queryString] = path.split('?');
  const query = new URLSearchParams(queryString || '');

  if (method === 'GET' && pathname === '/config/public') {
    return ok(mockConfig);
  }

  if (method === 'GET' && pathname === '/auth/csrf') {
    return ok({ csrf_token: 'mock-csrf-token' });
  }

  if (method === 'GET' && pathname === '/auth/me') {
    return ok({
      user: mockSessionUser,
      csrf_token: 'mock-csrf-token',
      unread_notifications: 0,
      unread_messages: 0,
    });
  }

  if (method === 'GET' && pathname === '/products') {
    const perPage = Number(query.get('per_page')) || 12;
    const page = Number(query.get('page')) || 1;
    const total = mockProducts.length;
    return ok(mockProducts, { page, per_page: perPage, total, total_pages: 1 });
  }

  const productMatch = pathname.match(/^\/products\/(\d+)$/);
  if (method === 'GET' && productMatch) {
    const product = productById(productMatch[1]);
    if (!product) {
      return fail('NOT_FOUND', 'Produk tidak ditemukan.', 404);
    }
    return ok(product);
  }

  if (method === 'POST' && pathname === '/auth/login') {
    const { email, password } = body || {};
    if (email === 'mock@mail.com' && password === 'mock12345') {
      mockSessionUser = {
        id: 1,
        username: 'Mock',
        email,
        phone: null,
        role: 'USER',
        store_status: null,
      };
      return ok({
        user: mockSessionUser,
        csrf_token: 'mock-csrf-token',
      });
    }
    return fail('INVALID_CREDENTIALS', 'Email atau password salah.', 401);
  }

  if (method === 'POST' && pathname === '/auth/register') {
    const { username, email, password } = body || {};
    if (String(password || '').length < 8) {
      return fail('VALIDATION_ERROR', 'Periksa kembali isian Anda.', 422, {
        password: 'Password minimal 8 karakter.',
      });
    }
    mockSessionUser = {
      id: mockProducts.length + 2,
      username,
      email,
      phone: null,
      role: 'USER',
      store_status: null,
    };
    return ok({ ...mockSessionUser });
  }

  const storeApplicationMatch = pathname.match(/^\/stores\/me\/application$/);
  if (method === 'GET' && storeApplicationMatch) {
    return ok(mockStoreApplication);
  }

  if (method === 'POST' && pathname === '/stores') {
    const fields = fieldsFrom(body);
    const { store_name, category, description, whatsapp, address, ktp_image, logo_image } = fields;
    if (!store_name || !category || !address || !ktp_image) {
      return fail(
        'VALIDATION_ERROR',
        'Lengkapi field wajib sebelum mengirim.',
        422,
        { store_name: 'Nama toko wajib.', category: 'Kategori wajib.', address: 'Alamat wajib.' }
      );
    }
    mockStoreApplication = {
      status: 'PENDING',
      store_name,
      created_at: '2026-10-29 10:00:00',
    };
    mockSessionUser = { ...mockSessionUser, store_status: 'PENDING' };
    return ok({ id: 2, status: 'PENDING' });
  }

  return fail('NOT_FOUND', 'Endpoint mock tidak dikenal.', 404);
}
