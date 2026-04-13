import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "../hooks/AuthContext";

function resolveHomePath(user, roles) {
  if (user?.is_super_admin || roles.includes("platform-super-admin")) {
    return "/super-admin/dashboard";
  }

  if (roles.length > 0) {
    return "/tenant/dashboard";
  }

  return "/login";
}

export function GuestOnlyRoute() {
  const { isAuth, hydrating, roles, user } = useAuth();

  if (hydrating) {
    return <div className="screen-state">Chargement de la session...</div>;
  }

  if (isAuth) {
    return <Navigate to={resolveHomePath(user, roles)} replace />;
  }

  return <Outlet />;
}

export function ProtectedRoute() {
  const { isAuth, hydrating } = useAuth();
  const location = useLocation();

  if (hydrating) {
    return <div className="screen-state">Chargement de la session...</div>;
  }

  if (!isAuth) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  return <Outlet />;
}

export function RoleRoute({ allow = [] }) {
  const { roles, user } = useAuth();

  if (allow.length === 0) {
    return <Outlet />;
  }

  const granted = allow.some((role) => roles.includes(role));

  if (user?.is_super_admin && allow.includes("platform-super-admin")) {
    return <Outlet />;
  }

  if (!granted) {
    return <Navigate to={resolveHomePath(user, roles)} replace />;
  }

  return <Outlet />;
}

export function resolveDefaultPath(user, roles) {
  return resolveHomePath(user, roles);
}
