import { Navigate } from "react-router-dom";
import { useAuth } from "../../hooks/AuthContext";
import { getDefaultRouteForUser } from "../../utils/auth";

export default function DashboardRedirect() {
  const { user } = useAuth();
  return <Navigate to={getDefaultRouteForUser(user)} replace />;
}
