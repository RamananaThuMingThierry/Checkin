import React, { useEffect, useMemo, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/AuthContext";
import { useI18n } from "../hooks/I18nContext";
import { resolveDefaultPath } from "../route/guards";

function buildAvatarUrl(path) {
  if (!path) {
    return null;
  }

  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  const apiUrl = import.meta.env.VITE_API_URL || `${window.location.origin}/api/v1`;
  const base = apiUrl.replace(/\/api\/?v?\d*\/?$/, "");

  return `${base}/${String(path).replace(/^\/+/, "")}`;
}

function buildNavigation(roles) {
  if (roles.includes("platform-super-admin")) {
    return [
      { to: "/super-admin/dashboard", icon: "bi-speedometer2", label: "Tableau de bord" },
      { to: "/super-admin/dashboard", icon: "bi-building-gear", label: "Pilotage plateforme" },
    ];
  }

  return [
    { to: "/tenant/dashboard", icon: "bi-speedometer2", label: "Tableau de bord" },
    { to: "/tenant/dashboard", icon: "bi-clock-history", label: "Pointages" },
  ];
}

function SidebarItem({ item, collapsed, onAction }) {
  const { to, label, action, icon } = item;

  if (action) {
    return (
      <button
        type="button"
        title={collapsed ? label : undefined}
        className="nav-link d-flex align-items-center px-2 py-2 rounded-3 mb-1 text-light sidebar-link w-100 border-0 bg-transparent text-start"
        onClick={() => onAction(action)}
      >
        <span className="d-flex align-items-center gap-2 w-100">
          <span className="sidebar-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
            <i className={`bi ${icon}`} />
          </span>

          {!collapsed ? (
            <span className="fw-medium text-truncate" style={{ maxWidth: 160 }}>
              {label}
            </span>
          ) : null}
        </span>
      </button>
    );
  }

  return (
    <NavLink
      to={to}
      title={collapsed ? label : undefined}
      className={({ isActive }) =>
        [
          "nav-link",
          "d-flex",
          "align-items-center",
          "px-2",
          "py-2",
          "rounded-3",
          "mb-1",
          isActive ? "active bg-warning text-dark" : "text-light sidebar-link",
        ].join(" ")
      }
    >
      <span className="d-flex align-items-center gap-2 w-100">
        <span className="sidebar-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0">
          <i className={`bi ${icon}`} />
        </span>

        {!collapsed ? (
          <span className="fw-medium text-truncate" style={{ maxWidth: 160 }}>
            {label}
          </span>
        ) : null}
      </span>
    </NavLink>
  );
}

function ConfirmModal({ open, title, message, confirmText, loading, onCancel, onConfirm }) {
  if (!open) {
    return null;
  }

  return (
    <>
      <div className="modal fade show" style={{ display: "block" }} aria-modal="true" role="dialog">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content border-0 shadow">
            <div className="modal-header">
              <h5 className="modal-title">{title}</h5>
              <button type="button" className="btn-close" onClick={onCancel} disabled={loading} />
            </div>

            <div className="modal-body">
              <p className="mb-0">{message}</p>
            </div>

            <div className="modal-footer">
              <button className="btn btn-outline-secondary" onClick={onCancel} disabled={loading}>
                Annuler
              </button>
              <button className="btn btn-danger" onClick={onConfirm} disabled={loading}>
                {loading ? "Deconnexion..." : confirmText}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div className="modal-backdrop fade show" onClick={loading ? undefined : onCancel} />
    </>
  );
}

export default function AdminLayout() {
  const { lang, setLang, supported } = useI18n();
  const { isAuth, logout, roles, user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const [drawerOpen, setDrawerOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(false);
  const [logoutOpen, setLogoutOpen] = useState(false);
  const [logoutLoading, setLogoutLoading] = useState(false);
  const [notificationsOpen, setNotificationsOpen] = useState(false);

  const navigation = useMemo(() => buildNavigation(roles), [roles]);
  const sidebarWidth = useMemo(() => (collapsed ? 84 : 280), [collapsed]);
  const headerAvatar = buildAvatarUrl(user?.avatar);
  const defaultPath = resolveDefaultPath(user, roles);
  const isPlatform = roles.includes("platform-super-admin");

  useEffect(() => {
    const onKeyDown = (event) => {
      if (event.key === "Escape") {
        setDrawerOpen(false);
        setLogoutOpen(false);
        setNotificationsOpen(false);
      }
    };

    window.addEventListener("keydown", onKeyDown);

    return () => window.removeEventListener("keydown", onKeyDown);
  }, []);

  useEffect(() => {
    if (!isAuth) {
      return;
    }

    if (isPlatform && location.pathname.startsWith("/super-admin")) {
      return;
    }

    if (!isPlatform && location.pathname.startsWith("/tenant")) {
      return;
    }

    navigate(defaultPath, { replace: true });
  }, [defaultPath, isAuth, isPlatform, location.pathname, navigate]);

  useEffect(() => {
    const lockScroll = drawerOpen || logoutOpen;
    document.body.style.overflow = lockScroll ? "hidden" : "";

    return () => {
      document.body.style.overflow = "";
    };
  }, [drawerOpen, logoutOpen]);

  useEffect(() => {
    setDrawerOpen(false);
    setNotificationsOpen(false);
  }, [location.pathname]);

  function handleAction(action) {
    if (action === "logout") {
      setLogoutOpen(true);
    }
  }

  async function confirmLogout() {
    setLogoutLoading(true);

    try {
      logout();
      setLogoutOpen(false);
      navigate("/login", { replace: true });
    } finally {
      setLogoutLoading(false);
    }
  }

  return (
    <div className="admin-page" style={{ "--admin-sidebar-width": `${sidebarWidth}px` }}>
      <div className="admin-shell">
        <aside className="sidebar desktop d-none d-lg-flex flex-column p-3" style={{ width: sidebarWidth }}>
          <div className="d-flex align-items-center justify-content-between mb-3">
            <div className="sidebar-header d-flex align-items-center gap-2">
              <img src="/images/logo/check-in.png" alt="Pointages" className="admin-brand-icon" />
              {!collapsed ? <div className="fw-bold text-warning fs-5">Pointages</div> : null}
            </div>
          </div>

          <nav className="mt-2 sidebar-nav">
            {navigation.map((item) => (
              <SidebarItem
                key={item.to ?? item.action}
                item={item}
                collapsed={collapsed}
                onAction={handleAction}
              />
            ))}
          </nav>

          <div className="mt-auto pt-3 border-top border-secondary sidebar-footer">
            <button
              type="button"
              className={`btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2 ${
                collapsed ? "px-2" : ""
              }`}
              onClick={() => handleAction("logout")}
              title={collapsed ? "Deconnexion" : undefined}
            >
              {!collapsed ? <span> <i className="bi bi-box-arrow-right me-2" />Deconnexion</span> : null}
            </button>
          </div>
        </aside>

        {drawerOpen ? <div className="drawer-overlay" onClick={() => setDrawerOpen(false)} /> : null}

        <aside className={`sidebar-drawer d-lg-none ${drawerOpen ? "open" : ""}`}>
          <div className="p-3 sidebar-drawer-inner d-flex flex-column">
            <div className="d-flex align-items-start justify-content-between mb-3">
              <div className="d-flex align-items-center gap-2">
                <img src="/images/logo/check-in.png" alt="Pointages" className="admin-brand-icon" />
                <div className="fw-bold text-warning fs-5">Pointages</div>
              </div>

              <button className="btn btn-sm btn-outline-light" type="button" onClick={() => setDrawerOpen(false)}>
                <i className="bi bi-x-lg" />
              </button>
            </div>

            <nav className="mt-2 sidebar-nav">
              {navigation.map((item) => (
                <SidebarItem
                  key={item.to ?? item.action}
                  item={item}
                  collapsed={false}
                  onAction={handleAction}
                />
              ))}
            </nav>

            <div className="mt-auto pt-3 border-top border-secondary sidebar-footer">
              <button
                type="button"
                className="btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2"
                onClick={() => handleAction("logout")}
              >
                <i className="bi bi-box-arrow-right" />
                <span>Deconnexion</span>
              </button>
            </div>
          </div>
        </aside>

        <div className="admin-content">
          <header className="bg-white border-bottom">
            <div className="container-fluid py-3 px-3 px-md-4">
              <div className="d-flex align-items-center justify-content-between gap-2 flex-nowrap admin-header-line">
                <div className="d-flex align-items-center gap-2 flex-nowrap admin-header-primary">
                    <button
                      className="btn btn-outline-dark btn-sm d-lg-none"
                      type="button"
                      onClick={() => setDrawerOpen(true)}
                    >
                      <i className="bi bi-list" />
                    </button>

                    <button
                      className="btn btn-outline-dark btn-sm d-none d-lg-inline-flex"
                      type="button"
                      onClick={() => setCollapsed((value) => !value)}
                      title={collapsed ? "Etendre le menu" : "Reduire le menu"}
                    >
                      <i className={`bi ${collapsed ? "bi-layout-sidebar-inset" : "bi-layout-sidebar"}`} />
                    </button>

                    <span className="fw-semibold d-none d-sm-inline">Dashboard</span>
                    <span className="badge bg-warning text-dark d-none d-md-inline-flex">
                      {isPlatform ? "Super admin" : "Tenant"}
                    </span>
                </div>

                <div className="d-flex align-items-center justify-content-end gap-2 position-relative flex-nowrap admin-header-actions">
                    <div className="position-relative admin-notifications">
                      <button
                        className="btn btn-outline-dark btn-sm position-relative"
                        type="button"
                        onClick={() => setNotificationsOpen((value) => !value)}
                      >
                        <i className="bi bi-bell" />
                      </button>

                      {notificationsOpen ? (
                        <div
                          className="position-absolute end-0 mt-2 bg-white border rounded-4 shadow-sm admin-notifications__panel"
                          style={{ zIndex: 1050 }}
                        >
                          <div className="p-3 border-bottom">
                            <div className="fw-semibold">Notifications</div>
                            <div className="small text-secondary">Aucune notification disponible.</div>
                          </div>
                        </div>
                      ) : null}
                    </div>

                    <div className="dropdown">
                      <button
                        className="btn btn-outline-dark btn-sm dropdown-toggle admin-lang-btn"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        title="Langue"
                      >
                        <i className="bi bi-translate" />
                        <span className="d-none d-md-inline ms-2">{lang.toUpperCase()}</span>
                      </button>

                      <ul className="dropdown-menu dropdown-menu-end">
                        {supported.map((value) => (
                          <li key={value}>
                            <button
                              className={`dropdown-item ${lang === value ? "active" : ""}`}
                              onClick={() => setLang(value)}
                              type="button"
                            >
                              {value.toUpperCase()}
                            </button>
                          </li>
                        ))}
                      </ul>
                    </div>

                    <button
                      className="btn btn-warning btn-sm d-inline-flex align-items-center gap-2 admin-account-btn"
                      type="button"
                      onClick={() => navigate(defaultPath)}
                    >
                      {headerAvatar ? (
                        <span className="admin-header-avatar">
                          <img src={headerAvatar} alt={user?.name || "Profil"} />
                        </span>
                      ) : (
                        <span className="admin-header-avatar admin-header-avatar--fallback">
                          {(user?.name || "U").slice(0, 1).toUpperCase()}
                        </span>
                      )}
                      <span className="admin-account-label d-none d-md-inline">
                        {user?.name || "Mon compte"}
                      </span>
                    </button>
                </div>
              </div>
            </div>
          </header>

          <main className="container-fluid py-3 py-md-5 px-3 px-md-4 admin-main">
            <Outlet />
          </main>

          <footer className="admin-footer">
            <div className="container-fluid py-3 px-3 px-md-4 d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
              <div className="text-muted small">
                {new Date().getFullYear()} Pointages Admin Dashboard
              </div>
              <div className="text-muted small d-flex gap-3">
                <span>Securise</span>
                <span>Performance</span>
              </div>
            </div>
          </footer>
        </div>
      </div>

      <ConfirmModal
        open={logoutOpen}
        title="Deconnexion"
        message="Voulez-vous vraiment vous deconnecter ?"
        confirmText="Oui, se deconnecter"
        loading={logoutLoading}
        onCancel={() => setLogoutOpen(false)}
        onConfirm={confirmLogout}
      />
    </div>
  );
}
