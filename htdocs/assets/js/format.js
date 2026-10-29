const rupiahFormatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR',
  maximumFractionDigits: 0,
});

const dateTimeFormatter = new Intl.DateTimeFormat('id-ID', {
  day: '2-digit',
  month: 'long',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
});

function parseApiDateTime(value) {
  const raw = String(value || new Date().toISOString());
  const [datePart, timePart = '00:00:00'] = raw.split(/[T ]/);
  const [y, m, d] = datePart.split('-').map(Number);
  const [hh = 0, mm = 0, ss = 0] = timePart.split(':').map(Number);
  return new Date(y, (m || 1) - 1, d || 1, hh || 0, mm || 0, ss || 0);
}

export function formatRupiah(amount) {
  return rupiahFormatter.format(Number(amount) || 0);
}

export function formatDateTime(value) {
  return dateTimeFormatter.format(parseApiDateTime(value));
}
