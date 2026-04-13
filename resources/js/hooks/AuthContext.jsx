import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { loginApi, meApi } from "../api/auth";
import { setApiToken } from "../api/axios";

const AuthContext = createContext(null);

function readStoredUser() {
  try {
    return JSON.parse(localStorage.getItem("user") || "null");
  } catch {
    return null;
  }
}

function extractRoles(user) {
  const roleCodes = Array.isArray(user?.roles)
    ? user.roles.map((role) => role?.code).filter(Boolean)
    : [];

  if (user?.is_super_admin && !roleCodes.includes("platform-super-admin")) {
    roleCodes.unshift("platform-super-admin");
  }

  return roleCodes;
}

function persistAuth(token, user) {
  if (token) {
    localStorage.setItem("token", token);
  } else {
    localStorage.removeItem("token");
  }

  if (user) {
    localStorage.setItem("user", JSON.stringify(user));
  } else {
    localStorage.removeItem("user");
  }
}

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem("token") || "");
  const [user, setUser] = useState(() => readStoredUser());
  const [loading, setLoading] = useState(false);
  const [hydrating, setHydrating] = useState(() => !!localStorage.getItem("token"));

  const isAuth = Boolean(token);
  const roles = useMemo(() => extractRoles(user), [user]);

  useEffect(() => {
    setApiToken(token);
  }, [token]);

  useEffect(() => {
    let cancelled = false;

    async function hydrate() {
      if (!token) {
        setHydrating(false);
        return;
      }

      try {
        const nextUser = await meApi();

        if (!cancelled) {
          setUser(nextUser || null);
          persistAuth(token, nextUser || null);
        }
      } catch {
        if (!cancelled) {
          setToken("");
          setUser(null);
          persistAuth("", null);
        }
      } finally {
        if (!cancelled) {
          setHydrating(false);
        }
      }
    }

    hydrate();

    return () => {
      cancelled = true;
    };
  }, [token]);

  async function login(credentials) {
    setLoading(true);

    try {
      const data = await loginApi(credentials);
      const nextToken = data?.token || "";
      const nextUser = data?.user || null;

      setToken(nextToken);
      setUser(nextUser);
      persistAuth(nextToken, nextUser);

      return {
        ok: true,
        user: nextUser,
        roles: extractRoles(nextUser),
      };
    } catch (error) {
      return {
        ok: false,
        message: error?.response?.data?.message || "Connexion impossible.",
        errors: error?.response?.data?.errors || {},
      };
    } finally {
      setLoading(false);
    }
  }

  function logout() {
    setToken("");
    setUser(null);
    setHydrating(false);
    persistAuth("", null);
    setApiToken("");
  }

  const value = useMemo(
    () => ({
      user,
      token,
      roles,
      isAuth,
      loading,
      hydrating,
      login,
      logout,
      hasRole: (role) => roles.includes(role),
    }),
    [user, token, roles, isAuth, loading, hydrating]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used within AuthProvider.");
  }

  return context;
}
