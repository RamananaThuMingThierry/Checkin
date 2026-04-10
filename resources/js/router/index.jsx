import { Navigate, createBrowserRouter } from "react-router-dom";
import RootLayout from "../layouts/RooterLayout";
import AdminLayout from "../layouts/AdminLayout";
import { AuthGuard, GuestGuard, SuperAdminGuard } from "./guard";
import Login from "../pages/auth/Login";
import DashboardHome from "../pages/dashboard/Home";
import DashboardRedirect from "../pages/dashboard/Redirect";
import SuperAdminDashboardPage from "../pages/super-admin/DashboardPage";
import SuperAdminTenantsPage from "../pages/super-admin/TenantsPage";
import SuperAdminOffersPage from "../pages/super-admin/OffersPage";
import SuperAdminSubscriptionsPage from "../pages/super-admin/SubscriptionsPage";
import SuperAdminInvoicesPage from "../pages/super-admin/InvoicesPage";
import SuperAdminSettingsPage from "../pages/super-admin/SettingsPage";
import TenantLayout from "../layouts/TenantLayout";
import TenantOverviewPage from "../pages/tenant/OverviewPage";
import TenantLeavesPage from "../pages/tenant/LeavesPage";
import TenantPlanningPage from "../pages/tenant/PlanningPage";
import TenantReportingPage from "../pages/tenant/ReportingPage";

export const router = createBrowserRouter([
  {
    path: "/",
    element: <RootLayout />,
    children: [
      {
        element: <GuestGuard />,
        children: [
          {
            index: true,
            element: <Navigate to="/login" replace />,
          },
          {
            path: "login",
            element: <Login />,
          },
        ],
      },
      {
        element: <AuthGuard />,
        children: [
          {
            path: "dashboard",
            element: <DashboardRedirect />,
          },
          {
            element: <SuperAdminGuard />,
            children: [
              {
                path: "super-admin",
                element: <AdminLayout />,
                children: [
                  {
                    index: true,
                    element: <Navigate to="/super-admin/dashboard" replace />,
                  },
                  {
                    path: "dashboard",
                    element: <SuperAdminDashboardPage />,
                  },
                  {
                    path: "tenants",
                    element: <SuperAdminTenantsPage />,
                  },
                  {
                    path: "offers",
                    element: <SuperAdminOffersPage />,
                  },
                  {
                    path: "subscriptions",
                    element: <SuperAdminSubscriptionsPage />,
                  },
                  {
                    path: "invoices",
                    element: <SuperAdminInvoicesPage />,
                  },
                  {
                    path: "settings",
                    element: <SuperAdminSettingsPage />,
                  },
                ],
              },
            ],
          },
          {
            path: "tenant-dashboard",
            element: <TenantLayout />,
            children: [
              {
                index: true,
                element: <Navigate to="/tenant-dashboard/overview" replace />,
              },
              {
                path: "overview",
                element: <TenantOverviewPage />,
              },
              {
                path: "leaves",
                element: <TenantLeavesPage />,
              },
              {
                path: "planning",
                element: <TenantPlanningPage />,
              },
              {
                path: "reporting",
                element: <TenantReportingPage />,
              },
            ],
          },
        ],
      },
      {
        path: "*",
        element: <Navigate to="/login" replace />,
      },
    ],
  },
]);
