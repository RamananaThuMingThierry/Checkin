const metrics = [
  { label: "Tenants actifs", value: "24", hint: "Provisioning et suivi commercial" },
  { label: "Offres publiees", value: "06", hint: "Catalogue disponible" },
  { label: "Abonnements en cours", value: "41", hint: "Activation et renouvellement" },
  { label: "Factures ouvertes", value: "13", hint: "Actions billing a traiter" },
];

const workstreams = [
  "Creer un nouveau tenant et initialiser son espace client.",
  "Maintenir le catalogue d'offres et les modules vendables.",
  "Souscrire puis activer les modules rattaches a un abonnement.",
  "Generer les factures et suivre les paiements plateformes.",
];

export default function SuperAdminDashboardPage() {
  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-hero-card">
        <div>
          <span className="superadmin-page-card__eyebrow">Vue globale</span>
          <h2>Dashboard plateforme</h2>
          <p>
            Ce shell separe clairement les operations plateforme des parcours RH du tenant. La
            navigation expose uniquement les ecrans super-admin.
          </p>
        </div>
        <div className="superadmin-chip-list">
          <span className="superadmin-chip">API-first</span>
          <span className="superadmin-chip">Provisioning</span>
          <span className="superadmin-chip">Billing</span>
        </div>
      </section>

      <section className="superadmin-metric-grid">
        {metrics.map((metric) => (
          <article key={metric.label} className="superadmin-page-card">
            <span className="superadmin-page-card__eyebrow">{metric.label}</span>
            <strong className="superadmin-metric-value">{metric.value}</strong>
            <p>{metric.hint}</p>
          </article>
        ))}
      </section>

      <section className="superadmin-page-card">
        <span className="superadmin-page-card__eyebrow">Priorites</span>
        <h2>Flux couverts dans ce dashboard</h2>
        <div className="superadmin-list-grid">
          {workstreams.map((item) => (
            <article key={item} className="superadmin-list-card">
              <p>{item}</p>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
