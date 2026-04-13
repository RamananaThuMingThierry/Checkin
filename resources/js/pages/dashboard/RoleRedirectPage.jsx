import { Navigate } from "react-router-dom";
import { useAuth } from "../../hooks/AuthContext";
import { resolveDefaultPath } from "../../route/guards";

export default function RoleRedirectPage() {
  const { user, roles } = useAuth();

  return <Navigate to={resolveDefaultPath(user, roles)} replace />;
}
