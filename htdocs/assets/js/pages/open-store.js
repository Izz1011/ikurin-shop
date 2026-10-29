import { api } from '../api.js';
import { showToast } from '../components/toast.js';
import { mountNavbar } from '../components/navbar.js';
import { mountFooter } from '../components/footer.js';

function setSubmitting(isSubmitting) {
  const submitButton = document.getElementById('open-store-submit');
  if (!submitButton) return;
  submitButton.disabled = isSubmitting;
  submitButton.classList.toggle('btn--loading', isSubmitting);
}

function initOpenStorePage() {
  const form = document.getElementById('open-store-form');
  if (!form) return;

  const ktpInput = document.getElementById('store_ktp');
  const logoInput = document.getElementById('store_logo');
  const ktpPreview = document.getElementById('store_ktp-preview');
  const logoPreview = document.getElementById('store_logo-preview');

  const renderPreview = (input, previewNode) => {
    if (!input || !previewNode) return;
    const file = input.files?.[0];
    if (!file) {
      previewNode.textContent = '';
      return;
    }
    const previewUrl = URL.createObjectURL(file);
    const img = document.createElement('img');
    img.src = previewUrl;
    img.alt = file.name;
    img.style.maxWidth = '100%';
    previewNode.textContent = '';
    previewNode.appendChild(img);
  };

  ktpInput?.addEventListener('change', () => renderPreview(ktpInput, ktpPreview));
  logoInput?.addEventListener('change', () => renderPreview(logoInput, logoPreview));

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const storeName = document.getElementById('store_name').value.trim();
    const category = document.getElementById('store_category').value;
    const description = document.getElementById('store_description').value.trim();
    const whatsapp = document.getElementById('store_whatsapp').value.trim();
    const address = document.getElementById('store_address').value.trim();
    const ktpFile = ktpInput?.files?.[0];

    if (!storeName || !category || !address || !ktpFile) {
      showToast('Lengkapi field wajib sebelum mengirim.', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('store_name', storeName);
    formData.append('category', category);
    if (description) formData.append('description', description);
    if (whatsapp) formData.append('whatsapp', whatsapp);
    formData.append('address', address);
    formData.append('ktp_image', ktpFile);
    if (logoInput?.files?.[0]) formData.append('logo_image', logoInput.files[0]);

    setSubmitting(true);
    try {
      await api.post('/stores', formData, { isMultipart: true });
      window.location.href = '/profile.html';
    } catch (error) {
      showToast(error.message, 'error');
      setSubmitting(false);
    }
  });
}

if (document.getElementById('open-store-form')) {
  initOpenStorePage();
}

mountNavbar(document.getElementById('navbar'));
mountFooter(document.getElementById('footer'));
