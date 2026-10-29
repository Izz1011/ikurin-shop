// Runtime frontend configuration (FE-002).
// USE_MOCK=true selama M0–M2; diaktifkan false pada milestone integrasi (FE-010).
export const IKURIN_CONFIG = {
  API_BASE: '/api/v1',
  USE_MOCK: true,
};

window.IKURIN_CONFIG = IKURIN_CONFIG;
