import axios from "axios";

const baseURL = import.meta.env.VITE_API_URL ?? "/api/v1";

const api = axios.create({
  baseURL,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
});

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
    return;
  }

  delete api.defaults.headers.common.Authorization;
}

export const authApi = {
  async login(payload) {
    const response = await api.post("/auth/login", payload);
    const token = response.data?.data?.token ?? null;
    const user = response.data?.data?.user ?? null;

    setAuthToken(token);

    return { token, user };
  },

  async me() {
    const response = await api.get("/auth/me");
    return response.data?.data ?? null;
  },
};

export const tenantApi = {
  async create(payload) {
    const response = await api.post("/tenants", payload);
    return response.data?.data ?? null;
  },
};

export const offerApi = {
  async create(payload) {
    const response = await api.post("/super-admin/offers", payload);
    return response.data?.data ?? null;
  },

  async attachModule(offerId, payload) {
    const response = await api.post(`/super-admin/offers/${offerId}/modules`, payload);
    return response.data?.data ?? null;
  },
};

export const moduleApi = {
  async list() {
    const response = await api.get("/super-admin/modules");
    return response.data?.data ?? [];
  },
};

export const subscriptionApi = {
  async create(payload) {
    const response = await api.post("/super-admin/subscriptions", payload);
    return response.data?.data ?? null;
  },

  async activateModules(subscriptionId) {
    const response = await api.post(`/super-admin/subscriptions/${subscriptionId}/activate-modules`);
    return response.data?.data ?? [];
  },
};

export const invoiceApi = {
  async generate(subscriptionId, payload) {
    const response = await api.post(`/super-admin/subscriptions/${subscriptionId}/invoices`, payload);
    return response.data?.data ?? null;
  },

  async listByTenant(tenantId, params = {}) {
    const response = await api.get(`/super-admin/tenants/${tenantId}/invoices`, { params });
    return response.data?.data ?? [];
  },
};

export const paymentApi = {
  async create(invoiceId, payload) {
    const response = await api.post(`/super-admin/invoices/${invoiceId}/payments`, payload);
    return response.data?.data ?? null;
  },
};

export const leaveRequestApi = {
  async listByTenant(tenantEncryptedId, params = {}) {
    const response = await api.get(`/tenants/${tenantEncryptedId}/leave-requests`, { params });
    return response.data?.data ?? [];
  },

  async approve(leaveRequestEncryptedId) {
    const response = await api.post(`/leave-requests/${leaveRequestEncryptedId}/approve`);
    return response.data?.data ?? null;
  },

  async reject(leaveRequestEncryptedId, payload) {
    const response = await api.post(`/leave-requests/${leaveRequestEncryptedId}/reject`, payload);
    return response.data?.data ?? null;
  },
};

export const plannedAbsenceApi = {
  async listByTenant(tenantEncryptedId, params) {
    const response = await api.get(`/tenants/${tenantEncryptedId}/planned-absences`, { params });
    return response.data?.data ?? [];
  },
};

export const attendanceReportApi = {
  async listByTenant(tenantEncryptedId, params) {
    const response = await api.get(`/tenants/${tenantEncryptedId}/attendance-report`, { params });
    return response.data?.data ?? [];
  },

  async exportByTenant(tenantEncryptedId, params) {
    const response = await api.get(`/tenants/${tenantEncryptedId}/attendance-report/export`, {
      params,
      responseType: "blob",
    });

    return {
      blob: response.data,
      contentDisposition: response.headers["content-disposition"] ?? "",
      contentType: response.headers["content-type"] ?? "",
    };
  },
};

export default api;
