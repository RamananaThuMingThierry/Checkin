import { useMemo, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/AuthContext";

const NAV_ITEMS = [
  { to: "/tenant-dashboard/overview", label: "Overview", hint: "Vue d'ensemble tenant" },
  { to: "/tenant-dashboard/leaves", label: "Leaves", hint: "Validation des conges" },
  { to: "/tenant-dashboard/planning", label: "Planning", hint: "Absences planifiees" },
  { to: "/tenant-dashboard/reporting", label: "Reporting", hint: "Retards et absences" },
];

function getSection(pathname) {
  return NAV_ITEMS.find((item) => pathname.startsWith(item.to)) ?? null;
}

export default function TenantLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const currentSection = useMemo(() => getSection(location.pathname), [location.pathname]);

  function handleLogout() {
    logout();
    navigate("/login", { replace: true });
  }

  return (
    <div className="tenant-shell">
      <div
        className={`tenant-backdrop ${sidebarOpen ? "is-visible" : ""}`}
        onClick={() => setSidebarOpen(false)}
      />

      <aside className={`tenant-sidebar ${sidebarOpen ? "is-open" : ""}`}>
        <div className="tenant-sidebar__brand">
          <span className="tenant-sidebar__eyebrow">Tenant Admin</span>
          <strong>{user?.tenant?.name ?? "Entreprise"}</strong>
          <p>Operations RH et reporting du tenant depuis un shell dedie.</p>
        </div>

        <nav className="tenant-nav">
          {NAV_ITEMS.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) => `tenant-nav__link ${isActive ? "is-active" : ""}`}
              onClick={() => setSidebarOpen(false)}
            >
              <span>{item.label}</span>
              <small>{item.hint}</small>
            </NavLink>
          ))}
        </nav>

        <div className="tenant-sidebar__footer">
          <div className="tenant-session-card">
            <span>Compte</span>
            <strong>{user?.name ?? "Tenant Admin"}</strong>
            <small>{user?.email ?? "-"}</small>
          </div>

          <button type="button" className="btn btn-danger w-100" onClick={handleLogout}>
            Se deconnecter
          </button>
        </div>
      </aside>

      <div className="tenant-main">
        <header className="tenant-header">
          <div className="tenant-header__topbar">
            <button
              type="button"
              className="btn btn-outline-light tenant-menu-btn"
              onClick={() => setSidebarOpen((current) => !current)}
            >
              Menu
            </button>

            <div className="tenant-header__status">
              <span>Tenant scope</span>
              <strong>{user?.tenant?.code ?? "tenant"}</strong>
            </div>
          </div>

          <div className="tenant-header__body">
            <div>
              <span className="tenant-header__kicker">Operations RH</span>
              <h1>{currentSection?.label ?? "Tenant Dashboard"}</h1>
              <p>
                Shell dedie aux besoins RH du tenant avec navigation focalisee sur les conges, le
                planning et le reporting.
              </p>
            </div>

            <div className="tenant-header__identity">
              <span>Tenant</span>
              <strong>{user?.tenant?.name ?? "-"}</strong>
              <small>{user?.email ?? "-"}</small>
            </div>
          </div>
        </header>

        <main className="tenant-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
