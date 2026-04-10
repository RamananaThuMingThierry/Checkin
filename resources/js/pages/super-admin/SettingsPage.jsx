import { useState } from "react";

const initialSettings = {
  platformName: "Pointages",
  defaultCurrency: "MGA",
  defaultLocale: "fr",
  supportEmail: "support@pointages.test",
  maintenanceMode: false,
  exposePublicOffers: true,
  compactSidebar: false,
  billingPrefix: "INV",
  subscriptionPrefix: "SUB",
  defaultTimezone: "Indian/Antananarivo",
};

const settingNotes = [
  {
    title: "Etat backend",
    text: "Aucun endpoint plateforme dedie n'est encore expose. Cette page prepare la structure frontend.",
  },
  {
    title: "Perimetre actuel",
    text: "Les reglages concernent le shell super-admin et les conventions visibles du produit.",
  },
  {
    title: "Prochaine integration",
    text: "Le futur endpoint devra permettre lecture et mise a jour des parametres globaux.",
  },
];

export default function SuperAdminSettingsPage() {
  const [form, setForm] = useState(initialSettings);
  const [feedback, setFeedback] = useState({ type: "", message: "" });
  const [savedSnapshot, setSavedSnapshot] = useState(initialSettings);

  function handleChange(event) {
    const { name, type, checked, value } = event.target;

    setForm((current) => ({
      ...current,
      [name]: type === "checkbox" ? checked : value,
    }));
  }

  function handleSubmit(event) {
    event.preventDefault();
    setSavedSnapshot(form);
    setFeedback({
      type: "success",
      message:
        "Parametres enregistres localement. Le raccordement API plateforme reste a brancher.",
    });
  }

  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Settings</span>
          <h2>Parametres plateforme</h2>
          <p>
            Structure frontend des reglages globaux du shell super-admin, en attente du contrat API
            backend correspondant.
          </p>

          <form className="superadmin-form-grid mt-4" onSubmit={handleSubmit}>
            <label className="superadmin-field">
              <span>Nom produit</span>
              <input
                name="platformName"
                className="form-control superadmin-input"
                value={form.platformName}
                onChange={handleChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Email support</span>
              <input
                name="supportEmail"
                type="email"
                className="form-control superadmin-input"
                value={form.supportEmail}
                onChange={handleChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Devise par defaut</span>
              <select
                name="defaultCurrency"
                className="form-select superadmin-input"
                value={form.defaultCurrency}
                onChange={handleChange}
              >
                <option value="MGA">MGA</option>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
              </select>
            </label>

            <label className="superadmin-field">
              <span>Langue par defaut</span>
              <select
                name="defaultLocale"
                className="form-select superadmin-input"
                value={form.defaultLocale}
                onChange={handleChange}
              >
                <option value="fr">fr</option>
                <option value="en">en</option>
                <option value="ar">ar</option>
              </select>
            </label>

            <label className="superadmin-field">
              <span>Fuseau horaire</span>
              <input
                name="defaultTimezone"
                className="form-control superadmin-input"
                value={form.defaultTimezone}
                onChange={handleChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Prefixe facture</span>
              <input
                name="billingPrefix"
                className="form-control superadmin-input"
                value={form.billingPrefix}
                onChange={handleChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Prefixe abonnement</span>
              <input
                name="subscriptionPrefix"
                className="form-control superadmin-input"
                value={form.subscriptionPrefix}
                onChange={handleChange}
              />
            </label>

            <div className="superadmin-field superadmin-field--full">
              <span>Comportement</span>
              <div className="superadmin-toggle-row">
                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="maintenanceMode"
                    checked={form.maintenanceMode}
                    onChange={handleChange}
                  />
                  <span>maintenance mode</span>
                </label>

                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="exposePublicOffers"
                    checked={form.exposePublicOffers}
                    onChange={handleChange}
                  />
                  <span>offres publiques visibles</span>
                </label>

                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="compactSidebar"
                    checked={form.compactSidebar}
                    onChange={handleChange}
                  />
                  <span>sidebar compacte</span>
                </label>
              </div>
            </div>

            {feedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  feedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {feedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger">
                Enregistrer les parametres
              </button>
            </div>
          </form>
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Snapshot</span>
          <h2>Derniere configuration enregistree</h2>
          <dl className="superadmin-definition-list">
            <div>
              <dt>Produit</dt>
              <dd>{savedSnapshot.platformName}</dd>
            </div>
            <div>
              <dt>Devise</dt>
              <dd>{savedSnapshot.defaultCurrency}</dd>
            </div>
            <div>
              <dt>Langue</dt>
              <dd>{savedSnapshot.defaultLocale}</dd>
            </div>
            <div>
              <dt>Timezone</dt>
              <dd>{savedSnapshot.defaultTimezone}</dd>
            </div>
            <div>
              <dt>Facture</dt>
              <dd>{savedSnapshot.billingPrefix}</dd>
            </div>
            <div>
              <dt>Abonnement</dt>
              <dd>{savedSnapshot.subscriptionPrefix}</dd>
            </div>
          </dl>
        </article>
      </section>

      <section className="superadmin-page-card">
        <span className="superadmin-page-card__eyebrow">Integration</span>
        <h2>Etat de raccordement</h2>
        <div className="superadmin-list-grid">
          {settingNotes.map((item) => (
            <article key={item.title} className="superadmin-list-card">
              <strong>{item.title}</strong>
              <p>{item.text}</p>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
