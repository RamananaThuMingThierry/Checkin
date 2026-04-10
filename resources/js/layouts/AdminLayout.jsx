import { useEffect, useMemo, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/AuthContext";
import { useI18n } from "../hooks/I18nContext";

const NAV_ITEMS = [
  { to: "/super-admin/dashboard", label: "Dashboard", hint: "Vue plateforme" },
  { to: "/super-admin/tenants", label: "Tenants", hint: "Provisioning clients" },
  { to: "/super-admin/offers", label: "Offers", hint: "Catalogue commercial" },
  { to: "/super-admin/subscriptions", label: "Subscriptions", hint: "Activation SaaS" },
  { to: "/super-admin/invoices", label: "Invoices", hint: "Billing et paiements" },
  { to: "/super-admin/settings", label: "Settings", hint: "Parametrage global" },
];

const LOCALES = [
  { code: "fr", label: "FR" },
  { code: "en", label: "EN" },
  { code: "ar", label: "AR" },
];

function getSection(pathname) {
  return NAV_ITEMS.find((item) => pathname.startsWith(item.to)) ?? null;
}

function isDesktopViewport() {
  if (typeof window === "undefined") {
    return true;
  }

  return window.innerWidth >= 992;
}

function IconMenu() {
  return <span className="superadmin-menu-btn__icon" aria-hidden="true" />;
}

function IconGlobe() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="superadmin-action-icon">
      <path
        d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20m6.92 9h-3.03a15.5 15.5 0 0 0-1.38-5A8.03 8.03 0 0 1 18.92 11M12 4.04c.84 1.07 1.87 3.19 2.24 6H9.76c.37-2.81 1.4-4.93 2.24-6M9.49 6A15.5 15.5 0 0 0 8.11 11H5.08A8.03 8.03 0 0 1 9.49 6M5.08 13h3.03a15.5 15.5 0 0 0 1.38 5A8.03 8.03 0 0 1 5.08 13M12 19.96c-.84-1.07-1.87-3.19-2.24-6h4.48c-.37 2.81-1.4 4.93-2.24 6M14.51 18a15.5 15.5 0 0 0 1.38-5h3.03A8.03 8.03 0 0 1 14.51 18"
        fill="currentColor"
      />
    </svg>
  );
}

function IconUser() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="superadmin-action-icon">
      <path
        d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4m0 2c-3.33 0-6 1.79-6 4v2h12v-2c0-2.21-2.67-4-6-4"
        fill="currentColor"
      />
    </svg>
  );
}

function IconClose() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="superadmin-action-icon">
      <path
        d="m18.3 5.71l-1.41-1.42L12 9.17L7.11 4.29L5.7 5.71L10.59 10.6L5.7 15.49l1.41 1.42L12 12.01l4.89 4.9l1.41-1.42l-4.89-4.89z"
        fill="currentColor"
      />
    </svg>
  );
}

export default function AdminLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useAuth();
  const { locale, setLocale } = useI18n();
  const [isDesktop, setIsDesktop] = useState(isDesktopViewport);
  const [desktopSidebarVisible, setDesktopSidebarVisible] = useState(true);
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);
  const [langOpen, setLangOpen] = useState(false);

  const currentSection = useMemo(() => getSection(location.pathname), [location.pathname]);
  const sidebarVisible = isDesktop ? desktopSidebarVisible : mobileSidebarOpen;

  useEffect(() => {
    setLangOpen(false);

    if (!isDesktop) {
      setMobileSidebarOpen(false);
    }
  }, [isDesktop, location.pathname]);

  useEffect(() => {
    function handleResize() {
      setIsDesktop(isDesktopViewport());
    }

    window.addEventListener("resize", handleResize);

    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }, []);

  useEffect(() => {
    document.body.style.overflow = !isDesktop && mobileSidebarOpen ? "hidden" : "";

    return () => {
      document.body.style.overflow = "";
    };
  }, [isDesktop, mobileSidebarOpen]);

  useEffect(() => {
    function handleKeyDown(event) {
      if (event.key === "Escape") {
        setMobileSidebarOpen(false);
        setLangOpen(false);
      }
    }

    window.addEventListener("keydown", handleKeyDown);

    return () => {
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, []);

  function toggleSidebar() {
    if (isDesktop) {
      setDesktopSidebarVisible((current) => !current);
      return;
    }

    setMobileSidebarOpen((current) => !current);
  }

  function closeSidebar() {
    if (isDesktop) {
      setDesktopSidebarVisible(false);
      return;
    }

    setMobileSidebarOpen(false);
  }

  function handleLogout() {
    logout();
    navigate("/login", { replace: true });
  }

  return (
    <div className={`superadmin-shell ${sidebarVisible ? "sidebar-open" : "sidebar-collapsed"}`}>
      <div
        className={`superadmin-backdrop ${!isDesktop && mobileSidebarOpen ? "is-visible" : ""}`}
        onClick={closeSidebar}
      />

      <aside className={`superadmin-sidebar ${sidebarVisible ? "is-open" : ""}`}>
        <div className="superadmin-sidebar__head">
          <div className="superadmin-sidebar__brand">
            <span className="superadmin-sidebar__eyebrow">Pointages</span>
            <strong>Super Admin</strong>
            <p>Pilotage plateforme, onboarding client et facturation.</p>
          </div>

          <button
            type="button"
            className="superadmin-sidebar__close"
            aria-label="Fermer le menu"
            onClick={closeSidebar}
          >
            <IconClose />
          </button>
        </div>

        <nav className="superadmin-nav" aria-label="Navigation super admin">
          {NAV_ITEMS.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) => `superadmin-nav__link ${isActive ? "is-active" : ""}`}
            >
              <span>{item.label}</span>
              <small>{item.hint}</small>
            </NavLink>
          ))}
        </nav>

        <div className="superadmin-sidebar__footer">
          <div className="superadmin-session-card">
            <span>Connecte</span>
            <strong>{user?.name ?? "Super Admin"}</strong>
            <small>{user?.email ?? "-"}</small>
          </div>

          <button type="button" className="btn btn-danger w-100 superadmin-logout-btn" onClick={handleLogout}>
            Se deconnecter
          </button>
        </div>
      </aside>

      <div className="superadmin-main">
        <header className="superadmin-header">
          <div className="superadmin-header__topbar">
            <button
              type="button"
              className="btn btn-outline-light superadmin-menu-btn"
              onClick={toggleSidebar}
              aria-label={sidebarVisible ? "Masquer le menu" : "Afficher le menu"}
            >
              <IconMenu />
              <span>Menu</span>
            </button>

            <div className="superadmin-header__status">
              <span className="superadmin-header__status-label">Session</span>
              <strong>{user?.is_super_admin ? "Super Admin" : "Compte plateforme"}</strong>
            </div>

            <div className="superadmin-header__toolbar">
              <div className="superadmin-lang-switch">
                <button
                  type="button"
                  className="superadmin-icon-btn"
                  aria-label="Changer la langue"
                  onClick={() => setLangOpen((current) => !current)}
                >
                  <IconGlobe />
                  <span className="superadmin-icon-btn__label">{locale.toUpperCase()}</span>
                </button>

                {langOpen ? (
                  <div className="superadmin-lang-menu">
                    {LOCALES.map((item) => (
                      <button
                        key={item.code}
                        type="button"
                        className={`superadmin-lang-menu__item ${locale === item.code ? "is-active" : ""}`}
                        onClick={() => {
                          setLocale(item.code);
                          setLangOpen(false);
                        }}
                      >
                        <span>{item.label}</span>
                        <small>{item.code}</small>
                      </button>
                    ))}
                  </div>
                ) : null}
              </div>

              <button
                type="button"
                className="superadmin-icon-btn superadmin-account-btn"
                aria-label="Compte utilisateur"
              >
                <IconUser />
                <span className="superadmin-icon-btn__label">
                  {user?.name?.split(" ")[0] ?? "Compte"}
                </span>
              </button>
            </div>
          </div>

          <div className="superadmin-header__body">
            <div className="superadmin-header__copy">
              <span className="superadmin-header__kicker">Plateforme SaaS</span>
              <h1>{currentSection?.label ?? "Super Admin"}</h1>
              <p>
                Console dediee au super-admin pour gerer tenants, offres, abonnements et billing.
              </p>
            </div>

            <div className="superadmin-header__panel">
              <div className="superadmin-header__identity">
                <span>Compte actif</span>
                <strong>{user?.email ?? "-"}</strong>
              </div>

              <div className="superadmin-header__current">
                <span>Section</span>
                <strong>{currentSection?.hint ?? "Navigation plateforme"}</strong>
              </div>
            </div>
          </div>

          <nav className="superadmin-header__mobile-nav" aria-label="Navigation rapide">
            {NAV_ITEMS.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                className={({ isActive }) =>
                  `superadmin-header__mobile-link ${isActive ? "is-active" : ""}`
                }
              >
                {item.label}
              </NavLink>
            ))}
          </nav>
        </header>

        <main className="superadmin-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
