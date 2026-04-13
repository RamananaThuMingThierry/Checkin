import { useAuth } from "../../hooks/AuthContext";

export default function DashboardHomePage({ title, description }) {
  const { user } = useAuth();

  return (
    <section className="dashboard-panel container-fluid px-0">
      <div className="card border-0 shadow-sm dashboard-panel__hero">
        <div className="card-body p-3 p-md-4 p-xl-4">
          <div className="row g-3 g-xl-4 align-items-start">
            <div className="col-12 col-lg">
              <p className="dashboard-panel__eyebrow">Bienvenue</p>
              <h1 className="mb-0">{title}</h1>
              <p className="mb-0">{description}</p>
            </div>

            <div className="col-12 col-lg-4">
              <div className="dashboard-panel__meta h-100">
                <span>{user?.tenant?.name || "Contexte plateforme"}</span>
                <strong>{user?.branch?.name || "Aucune agence rattachee"}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row g-3 g-xl-4 dashboard-panel__grid">
        <div className="col-12 col-md-6 col-xl-4">
          <article className="card border-0 shadow-sm h-100">
            <div className="card-body p-3 p-md-4">
              <h2>Profil</h2>
              <p>{user?.name}</p>
              <span>{user?.email}</span>
            </div>
          </article>
        </div>

        <div className="col-12 col-md-6 col-xl-4">
          <article className="card border-0 shadow-sm h-100">
            <div className="card-body p-3 p-md-4">
              <h2>Tenant</h2>
              <p>{user?.tenant?.name || "Global"}</p>
              <span>{user?.tenant?.code || "plateforme"}</span>
            </div>
          </article>
        </div>

        <div className="col-12 col-md-6 col-xl-4">
          <article className="card border-0 shadow-sm h-100">
            <div className="card-body p-3 p-md-4">
              <h2>Statut</h2>
              <p>{user?.status || "active"}</p>
              <span>Derniere connexion: {user?.last_login_at || "maintenant"}</span>
            </div>
          </article>
        </div>
      </div>
    </section>
  );
}
