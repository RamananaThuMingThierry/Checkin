import { createBrowserRouter, Navigate } from "react-router-dom";
import AdminLayout from "../layouts/AdminLayout";
import PublicLayout from "../layouts/PublicLayout";
import LoginPage from "../pages/auth/LoginPage";
import DashboardHomePage from "../pages/dashboard/DashboardHomePage";
import RoleRedirectPage from "../pages/dashboard/RoleRedirectPage";
import { GuestOnlyRoute, ProtectedRoute, RoleRoute } from "./guards";

export const router = createBrowserRouter([
  {
    element: <GuestOnlyRoute />,
    children: [
      {
        element: <PublicLayout />,
        children: [
          { path: "/login", element: <LoginPage /> },
          { path: "/", element: <Navigate to="/login" replace /> },
        ],
      },
    ],
  },
  {
    element: <ProtectedRoute />,
    children: [
      {
        path: "/app",
        element: <RoleRedirectPage />,
      },
      {
        element: <RoleRoute allow={["platform-super-admin", "tenant-admin"]} />,
        children: [
          {
            element: <AdminLayout />,
            children: [
              {
                path: "/super-admin/dashboard",
                element: <RoleRoute allow={["platform-super-admin"]} />,
                children: [
                  {
                    index: true,
                    element: (
                      <DashboardHomePage
                        title="Pilotage plateforme"
                        description="Le shell super-admin est pret. La connexion ouvre maintenant un espace dedie pour le provisioning, les offres et le billing."
                      />
                    ),
                  },
                ],
              },
              {
                path: "/tenant/dashboard",
                element: <RoleRoute allow={["tenant-admin"]} />,
                children: [
                  {
                    index: true,
                    element: (
                      <DashboardHomePage
                        title="Pilotage entreprise"
                        description="Le shell tenant est pret. La connexion ouvre un espace stabilise pour brancher ensuite les ecrans RH et reporting."
                      />
                    ),
                  },
                ],
              },
            ],
          },
        ],
      },
    ],
  },
  {
    path: "*",
    element: <Navigate to="/login" replace />,
  },
]);
