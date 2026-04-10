import { useEffect, useState } from "react";
import { tenantApi } from "../../api/axios";
import { getWorkflowSnapshot, saveWorkflowEntry } from "../../utils/workflowStore";

const initialForm = {
  name: "",
  code: "",
  email: "",
  phone: "",
  address: "",
  billing_name: "",
  billing_email: "",
  billing_phone: "",
  billing_address: "",
  billing_city: "",
  billing_country: "",
  tax_identification_number: "",
  registration_number: "",
  currency: "MGA",
  status: "trial",
};

const tenantHighlights = [
  { title: "Creation tenant", text: "Point d'entree frontend pour le provisioning commercial." },
  { title: "Agence principale", text: "Preparation du tenant avant delegation a son admin." },
  { title: "Contexte client", text: "Code, devise, statut et informations utiles au cycle SaaS." },
];

function normalizeCode(value) {
  return value
    .toLowerCase()
    .trim()
    .replace(/\s+/g, "-")
    .replace(/[^a-z0-9-_]/g, "");
}

function buildPayload(form) {
  return Object.fromEntries(
    Object.entries(form).filter(([, value]) => value !== "")
  );
}

function extractApiError(error) {
  const validationErrors = error?.response?.data?.errors;

  if (validationErrors && typeof validationErrors === "object") {
    return {
      message: "Le formulaire contient des erreurs.",
      fields: validationErrors,
    };
  }

  return {
    message:
      error?.response?.data?.message ??
      "La creation du tenant a echoue. Veuillez reessayer.",
    fields: {},
  };
}

export default function SuperAdminTenantsPage() {
  const [form, setForm] = useState(initialForm);
  const [submitting, setSubmitting] = useState(false);
  const [createdTenant, setCreatedTenant] = useState(null);
  const [feedback, setFeedback] = useState({ type: "", message: "" });
  const [fieldErrors, setFieldErrors] = useState({});
  const [workflowTenant, setWorkflowTenant] = useState(null);

  useEffect(() => {
    setWorkflowTenant(getWorkflowSnapshot().tenant);
  }, []);

  function handleChange(event) {
    const { name, value } = event.target;

    setForm((current) => ({
      ...current,
      [name]: name === "code" ? normalizeCode(value) : value,
    }));

    setFieldErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSubmitting(true);
    setFeedback({ type: "", message: "" });
    setFieldErrors({});

    try {
      const tenant = await tenantApi.create(buildPayload(form));
      setCreatedTenant(tenant);
      setWorkflowTenant(tenant);
      saveWorkflowEntry("tenant", tenant);
      setFeedback({
        type: "success",
        message: "Entreprise creee avec succes.",
      });
      setForm(initialForm);
    } catch (error) {
      const extracted = extractApiError(error);
      setFeedback({ type: "error", message: extracted.message });
      setFieldErrors(extracted.fields);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-page-card">
        <span className="superadmin-page-card__eyebrow">Tenants</span>
        <h2>Creer une entreprise cliente</h2>
        <p>
          Ouvrez un nouveau tenant depuis le shell super-admin sans entrer dans les ecrans metier
          internes du client.
        </p>

        <form className="superadmin-form-grid mt-4" onSubmit={handleSubmit}>
          <label className="superadmin-field">
            <span>Nom de l'entreprise</span>
            <input
              name="name"
              className="form-control superadmin-input"
              value={form.name}
              onChange={handleChange}
              placeholder="Acme Corp"
              required
            />
            {fieldErrors.name ? <small>{fieldErrors.name[0]}</small> : null}
          </label>

          <label className="superadmin-field">
            <span>Code tenant</span>
            <input
              name="code"
              className="form-control superadmin-input"
              value={form.code}
              onChange={handleChange}
              placeholder="acme-corp"
              required
            />
            {fieldErrors.code ? <small>{fieldErrors.code[0]}</small> : null}
          </label>

          <label className="superadmin-field">
            <span>Email principal</span>
            <input
              name="email"
              type="email"
              className="form-control superadmin-input"
              value={form.email}
              onChange={handleChange}
              placeholder="hello@acme.test"
            />
            {fieldErrors.email ? <small>{fieldErrors.email[0]}</small> : null}
          </label>

          <label className="superadmin-field">
            <span>Telephone</span>
            <input
              name="phone"
              className="form-control superadmin-input"
              value={form.phone}
              onChange={handleChange}
              placeholder="+261 34 00 000 00"
            />
            {fieldErrors.phone ? <small>{fieldErrors.phone[0]}</small> : null}
          </label>

          <label className="superadmin-field superadmin-field--full">
            <span>Adresse</span>
            <input
              name="address"
              className="form-control superadmin-input"
              value={form.address}
              onChange={handleChange}
              placeholder="Adresse du siege ou du contact principal"
            />
            {fieldErrors.address ? <small>{fieldErrors.address[0]}</small> : null}
          </label>

          <label className="superadmin-field">
            <span>Nom de facturation</span>
            <input
              name="billing_name"
              className="form-control superadmin-input"
              value={form.billing_name}
              onChange={handleChange}
              placeholder="Acme Billing"
            />
          </label>

          <label className="superadmin-field">
            <span>Email de facturation</span>
            <input
              name="billing_email"
              type="email"
              className="form-control superadmin-input"
              value={form.billing_email}
              onChange={handleChange}
              placeholder="billing@acme.test"
            />
          </label>

          <label className="superadmin-field">
            <span>Telephone de facturation</span>
            <input
              name="billing_phone"
              className="form-control superadmin-input"
              value={form.billing_phone}
              onChange={handleChange}
              placeholder="+261 34 00 000 01"
            />
          </label>

          <label className="superadmin-field">
            <span>Adresse de facturation</span>
            <input
              name="billing_address"
              className="form-control superadmin-input"
              value={form.billing_address}
              onChange={handleChange}
              placeholder="Adresse de facturation"
            />
          </label>

          <label className="superadmin-field">
            <span>Ville</span>
            <input
              name="billing_city"
              className="form-control superadmin-input"
              value={form.billing_city}
              onChange={handleChange}
              placeholder="Antananarivo"
            />
          </label>

          <label className="superadmin-field">
            <span>Pays</span>
            <input
              name="billing_country"
              className="form-control superadmin-input"
              value={form.billing_country}
              onChange={handleChange}
              placeholder="Madagascar"
            />
          </label>

          <label className="superadmin-field">
            <span>NIF</span>
            <input
              name="tax_identification_number"
              className="form-control superadmin-input"
              value={form.tax_identification_number}
              onChange={handleChange}
              placeholder="Numero fiscal"
            />
          </label>

          <label className="superadmin-field">
            <span>RCS / Registre</span>
            <input
              name="registration_number"
              className="form-control superadmin-input"
              value={form.registration_number}
              onChange={handleChange}
              placeholder="Numero d'enregistrement"
            />
          </label>

          <label className="superadmin-field">
            <span>Devise</span>
            <select
              name="currency"
              className="form-select superadmin-input"
              value={form.currency}
              onChange={handleChange}
            >
              <option value="MGA">MGA</option>
              <option value="EUR">EUR</option>
              <option value="USD">USD</option>
            </select>
          </label>

          <label className="superadmin-field">
            <span>Statut</span>
            <select
              name="status"
              className="form-select superadmin-input"
              value={form.status}
              onChange={handleChange}
            >
              <option value="trial">trial</option>
              <option value="active">active</option>
              <option value="suspended">suspended</option>
              <option value="inactive">inactive</option>
            </select>
          </label>

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
            <button type="submit" className="btn btn-danger" disabled={submitting}>
              {submitting ? "Creation..." : "Creer le tenant"}
            </button>
          </div>
        </form>
      </section>

      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Resultat</span>
          <h2>Dernier tenant cree</h2>
          {createdTenant ? (
            <dl className="superadmin-definition-list">
              <div>
                <dt>ID</dt>
                <dd>{createdTenant.id}</dd>
              </div>
              <div>
                <dt>Nom</dt>
                <dd>{createdTenant.name}</dd>
              </div>
              <div>
                <dt>Code</dt>
                <dd>{createdTenant.code}</dd>
              </div>
              <div>
                <dt>Statut</dt>
                <dd>{createdTenant.status}</dd>
              </div>
              <div>
                <dt>Devise</dt>
                <dd>{createdTenant.currency}</dd>
              </div>
              <div>
                <dt>Email</dt>
                <dd>{createdTenant.email || "-"}</dd>
              </div>
            </dl>
          ) : (
            <p>Aucun tenant cree dans cette session.</p>
          )}
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Contexte</span>
          <h2>Workflow courant</h2>
          {workflowTenant ? (
            <div className="superadmin-context-chip mb-4">
              Tenant memorise: #{workflowTenant.id} - {workflowTenant.name}
            </div>
          ) : (
            <p>Aucun tenant memorise pour le moment.</p>
          )}
          <div className="superadmin-list-grid">
            {tenantHighlights.map((item) => (
              <article key={item.title} className="superadmin-list-card">
                <strong>{item.title}</strong>
                <p>{item.text}</p>
              </article>
            ))}
          </div>
        </article>
      </section>
    </div>
  );
}
