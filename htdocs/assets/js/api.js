// API client — satu-satunya tempat pemanggilan jaringan (FE-002).
import { IKURIN_CONFIG } from './config.js';
import { mockRequest } from './mock/handler.js';

export class ApiError extends Error {
  constructor({ code, message, fields = null, status }) {
    super(message);
    this.name = 'ApiError';
    this.code = code;
    this.message = message;
    this.fields = fields;
    this.status = status;
  }
}

let cachedCsrf = null;
let csrfRequest = null;

function getCsrfToken() {
  if (cachedCsrf) {
    return Promise.resolve(cachedCsrf);
  }
  if (!csrfRequest) {
    csrfRequest = fetch(`${IKURIN_CONFIG.API_BASE}/auth/csrf`)
      .then((res) => res.json())
      .then((json) => {
        cachedCsrf = json?.data?.csrf_token ?? null;
        return cachedCsrf;
      })
      .finally(() => {
        csrfRequest = null;
      });
  }
  return csrfRequest;
}

function buildUrl(path, query) {
  if (!query) {
    return path;
  }
  const queryString = new URLSearchParams(query).toString();
  return `${path}${queryString ? `?${queryString}` : ''}`;
}

async function request(path, { method = 'GET', body = null, isMultipart = false } = {}) {
  if (IKURIN_CONFIG.USE_MOCK) {
    const outcome = await mockRequest(path, { method, body });
    if (!outcome.ok) {
      throw new ApiError({
        code: outcome.error.code,
        message: outcome.error.message,
        fields: outcome.error.fields ?? null,
        status: outcome.status,
      });
    }
    return { data: outcome.data, meta: outcome.meta ?? null };
  }

  const url = `${IKURIN_CONFIG.API_BASE}${path}`;
  const options = { method, headers: {} };
  if (method !== 'GET') {
    const csrfToken = await getCsrfToken();
    options.headers['X-CSRF-Token'] = csrfToken;
  }
  if (body) {
    if (isMultipart) {
      options.body = body;
    } else {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }
  }

  const response = await fetch(url, options);
  const json = await response.json().catch(() => null);

  if (response.ok && json && json.success) {
    return { data: json.data, meta: json.meta ?? null };
  }

  const error = new ApiError({
    code: (json && json.error && json.error.code) || 'SERVER_ERROR',
    message: (json && json.error && json.error.message) || 'Terjadi kesalahan pada server.',
    fields: (json && json.error && json.error.fields) || null,
    status: response.status,
  });

  if (response.status === 401) {
    window.location.href = `/login.html?next=${encodeURIComponent(
      window.location.pathname + window.location.search
    )}`;
  }

  throw error;
}

export const api = {
  get: (path, query) => request(buildUrl(path, query), { method: 'GET' }),
  post: (path, body, { isMultipart = false } = {}) =>
    request(path, { method: 'POST', body, isMultipart }),
  patch: (path, body) => request(path, { method: 'PATCH', body }),
  del: (path) => request(path, { method: 'DELETE' }),
  upload: (path, formData) => request(path, { method: 'POST', body: formData, isMultipart: true }),
};
