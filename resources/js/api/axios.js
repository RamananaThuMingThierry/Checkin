import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || `${window.location.origin}/api/v1`,
  headers: {
    Accept: "application/json",
  },
});

let inMemoryToken = localStorage.getItem("token") || "";

export function setApiToken(token) {
  inMemoryToken = token || "";
}

api.interceptors.request.use((config) => {
  const token = inMemoryToken || localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      setApiToken("");
    }

    return Promise.reject(error);
  }
);

export default api;
