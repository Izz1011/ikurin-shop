import { api } from '../api.js';
import { showToast } from '../components/toast.js';
import { mountNavbar } from '../components/navbar.js';
import { mountFooter } from '../components/footer.js';

function setSubmitting(isSubmitting) {
  const submitButton = document.getElementById('login-submit');
  if (!submitButton) return;
  submitButton.disabled = isSubmitting;
  submitButton.classList.toggle('btn--loading', isSubmitting);
}

function initLoginPage() {
  const form = document.getElementById('login-form');
  const errorNode = document.getElementById('login-error');
  if (!form) return;

  const redirectTarget = new URLSearchParams(window.location.search).get('next') || '/';

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    setSubmitting(true);
    try {
      await api.post('/auth/login', { email, password });
      window.location.href = redirectTarget;
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

if (document.getElementById('login-form')) {
  initLoginPage();
}

mountNavbar(document.getElementById('navbar'));
mountFooter(document.getElementById('footer'));
