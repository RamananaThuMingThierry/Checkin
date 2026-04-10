import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "../hooks/AuthContext";
import { getDefaultRouteForUser } from "../utils/auth";

export function GuestGuard() {
  const { booting, isAuthenticated, user } = useAuth();

  if (booting) {
    return null;
  }

  if (isAuthenticated) {
    return <Navigate to={getDefaultRouteForUser(user)} replace />;
  }

  return <Outlet />;
}

export function AuthGuard() {
  const { booting, isAuthenticated } = useAuth();
  const location = useLocation();

  if (booting) {
    return null;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return <Outlet />;
}

export function SuperAdminGuard() {
  const { booting, isAuthenticated, isSuperAdmin } = useAuth();
  const location = useLocation();

  if (booting) {
    return null;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  if (!isSuperAdmin) {
    return <Navigate to="/tenant-dashboard" replace />;
  }

  return <Outlet />;
}
