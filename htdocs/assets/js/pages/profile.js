import { api } from '../api.js';
import { requireAuth } from '../guard.js';
import { showToast } from '../components/toast.js';
import { mountNavbar } from '../components/navbar.js';
import { mountFooter } from '../components/footer.js';

function renderUser(user) {
  const card = document.getElementById('profile-card');
  while (card.firstChild) {
    card.removeChild(card.firstChild);
  }
  const title = document.createElement('h2');
  title.textContent = 'Akun';
  const username = document.createElement('p');
  username.textContent = `Username: ${user?.username ?? '-'}`;
  const email = document.createElement('p');
  email.textContent = `Email: ${user?.email ?? '-'}`;
  const phone = document.createElement('p');
  phone.textContent = `No. HP: ${user?.phone ?? '-'}`;
  const role = document.createElement('p');
  role.textContent = `Role: ${user?.role ?? '-'}`;
  const storeStatus = document.createElement('p');
  storeStatus.textContent = `Status Toko: ${user?.store_status ?? 'Belum Ada'}`;
  card.append(title, username, email, phone, role, storeStatus);
}

function renderApplication(application) {
  const section = document.getElementById('store-application');
  while (section.firstChild) {
    section.removeChild(section.firstChild);
  }
  const status = document.createElement('h2');
  status.textContent = 'Status Pengajuan Toko';
  const statusValue = document.createElement('p');
  statusValue.textContent = application ? `Status: ${application.status ?? '-'}` : 'Belum ada pengajuan toko.';
  const storeName = document.createElement('p');
  storeName.textContent = application?.store_name ? `Nama Toko: ${application.store_name}` : '';
  const rejectionReason = document.createElement('p');
  rejectionReason.textContent = application?.rejection_reason ? `Alasan: ${application.rejection_reason}` : '';
  section.append(status, statusValue, storeName, rejectionReason);
}

async function initProfilePage() {
  const user = await requireAuth();
  if (!user) {
    return;
  }
  renderUser(user);

  try {
    const { data: application } = await api.get('/stores/me/application');
    renderApplication(application);
  } catch (error) {
    showToast(error.message, 'error');
  }
}

if (document.getElementById('profile-card')) {
  initProfilePage();
}

mountNavbar(document.getElementById('navbar'));
mountFooter(document.getElementById('footer'));
