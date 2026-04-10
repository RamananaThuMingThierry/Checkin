const tenantMetrics = [
  { label: "Demandes a traiter", value: "12", hint: "Conge en attente" },
  { label: "Absences planifiees", value: "08", hint: "Vue planning" },
  { label: "Retards du jour", value: "03", hint: "Suivi operationnel" },
];

export default function TenantOverviewPage() {
  return (
    <div className="tenant-page-stack">
      <section className="tenant-page-card">
        <span className="tenant-page-card__eyebrow">Overview</span>
        <h2>Dashboard tenant</h2>
        <p>
          Ce shell ouvre le parcours RH du tenant et prepare les ecrans de traitement deja
          documentes dans le backlog frontend.
        </p>
      </section>

      <section className="tenant-metric-grid">
        {tenantMetrics.map((metric) => (
          <article key={metric.label} className="tenant-page-card">
            <span className="tenant-page-card__eyebrow">{metric.label}</span>
            <strong className="tenant-metric-value">{metric.value}</strong>
            <p>{metric.hint}</p>
          </article>
        ))}
      </section>
    </div>
  );
}
