import { api } from '../api.js';
import { showToast } from '../components/toast.js';
import { mountNavbar } from '../components/navbar.js';
import { mountFooter } from '../components/footer.js';

function setSubmitting(isSubmitting) {
  const submitButton = document.getElementById('register-submit');
  if (!submitButton) return;
  submitButton.disabled = isSubmitting;
  submitButton.classList.toggle('btn--loading', isSubmitting);
}

function initRegisterPage() {
  const form = document.getElementById('register-form');
  const errorNode = document.getElementById('register-error');
  if (!form) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const username = document.getElementById('register-username').value.trim();
    const email = document.getElementById('register-email').value.trim();
    const password = document.getElementById('register-password').value;
    const phone = document.getElementById('register-phone').value.trim();
    setSubmitting(true);
    try {
      await api.post('/auth/register', { username, email, password, phone: phone || undefined });
      window.location.href = '/';
    } catch (error) {
      if (errorNode) {
        errorNode.textContent = error.message;
        errorNode.hidden = false;
      }
      showToast(error.message, 'error');
      setSubmitting(false);
    }
  });
}

if (document.getElementById('register-form')) {
  initRegisterPage();
}

mountNavbar(document.getElementById('navbar'));
mountFooter(document.getElementById('footer'));
