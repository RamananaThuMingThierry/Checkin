export default function DashboardHome() {
  return (
    <div className="superadmin-page-card">
      <span className="superadmin-page-card__eyebrow">Dashboard</span>
      <h2>Zone non super-admin</h2>
      <p>
        Cette route reste disponible pour les parcours futurs des tenants. Le shell super-admin
        utilise maintenant les routes dediees sous <code>/super-admin</code>.
      </p>
    </div>
  );
}
