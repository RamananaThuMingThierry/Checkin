import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { authApi, setAuthToken } from "../api/axios";
import { getRoleCodes, isSuperAdminUser } from "../utils/auth";

const AUTH_STORAGE_KEY = "pointages.auth";

const AuthContext = createContext(null);

function readStoredSession() {
  if (typeof window === "undefined") {
    return { token: null, user: null };
  }

  try {
    const raw = window.localStorage.getItem(AUTH_STORAGE_KEY);

    if (!raw) {
      return { token: null, user: null };
    }

    const parsed = JSON.parse(raw);
    return {
      token: parsed?.token ?? null,
      user: parsed?.user ?? null,
    };
  } catch {
    return { token: null, user: null };
  }
}

function persistSession(session) {
  if (typeof window === "undefined") {
    return;
  }

  if (!session?.token) {
    window.localStorage.removeItem(AUTH_STORAGE_KEY);
    return;
  }

  window.localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(session));
}

export function AuthProvider({ children }) {
  const [session, setSession] = useState(() => readStoredSession());
  const [booting, setBooting] = useState(true);

  useEffect(() => {
    const token = session?.token ?? null;
    setAuthToken(token);
    persistSession(session);
  }, [session]);

  useEffect(() => {
    let active = true;

    async function hydrateSession() {
      const stored = readStoredSession();

      if (!stored.token) {
        if (active) {
          setBooting(false);
        }
        return;
      }

      setAuthToken(stored.token);

      try {
        const user = await authApi.me();

        if (!active) {
          return;
        }

        setSession({
          token: stored.token,
          user,
        });
      } catch {
        if (!active) {
          return;
        }

        setAuthToken(null);
        setSession({ token: null, user: null });
      } finally {
        if (active) {
          setBooting(false);
        }
      }
    }

    hydrateSession();

    return () => {
      active = false;
    };
  }, []);

  const value = useMemo(() => {
    async function login(credentials) {
      const authenticated = await authApi.login(credentials);
      setSession(authenticated);
      return authenticated.user;
    }

    function logout() {
      setAuthToken(null);
      setSession({ token: null, user: null });
    }

    return {
      token: session?.token ?? null,
      user: session?.user ?? null,
      roleCodes: getRoleCodes(session?.user),
      isAuthenticated: Boolean(session?.token),
      isSuperAdmin: isSuperAdminUser(session?.user),
      booting,
      login,
      logout,
    };
  }, [booting, session]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider.");
  }

  return context;
}
