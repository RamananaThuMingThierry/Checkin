import api from "./axios";

function unwrap(response) {
  return response?.data ?? response;
}

export async function loginApi(payload) {
  const { data } = await api.post("/auth/login", payload);
  return unwrap(data);
}

export async function meApi() {
  const { data } = await api.get("/auth/me");
  return unwrap(data);
}
