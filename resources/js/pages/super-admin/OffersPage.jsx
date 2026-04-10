import { useEffect, useState } from "react";
import { moduleApi, offerApi } from "../../api/axios";
import { getWorkflowSnapshot, saveWorkflowEntry } from "../../utils/workflowStore";

const initialOfferForm = {
  name: "",
  code: "",
  description: "",
  monthly_price: "",
  yearly_price: "",
  currency: "MGA",
  max_users: "",
  max_branches: "",
  max_employees: "",
  max_devices: "",
  is_public: false,
  is_active: true,
  is_custom: false,
};

const initialAttachForm = {
  offer_id: "",
  module_id: "",
  is_included: true,
};

function normalizeCode(value) {
  return value
    .toLowerCase()
    .trim()
    .replace(/\s+/g, "-")
    .replace(/[^a-z0-9-_]/g, "");
}

function toPayload(form) {
  return Object.fromEntries(
    Object.entries(form).filter(([, value]) => value !== "")
  );
}

function extractApiError(error, fallbackMessage) {
  const validationErrors = error?.response?.data?.errors;

  if (validationErrors && typeof validationErrors === "object") {
    return {
      message: "Le formulaire contient des erreurs.",
      fields: validationErrors,
    };
  }

  return {
    message: error?.response?.data?.message ?? fallbackMessage,
    fields: {},
  };
}

export default function SuperAdminOffersPage() {
  const [modules, setModules] = useState([]);
  const [modulesLoading, setModulesLoading] = useState(true);

  const [offerForm, setOfferForm] = useState(initialOfferForm);
  const [offerSubmitting, setOfferSubmitting] = useState(false);
  const [offerErrors, setOfferErrors] = useState({});
  const [offerFeedback, setOfferFeedback] = useState({ type: "", message: "" });
  const [createdOffer, setCreatedOffer] = useState(null);

  const [attachForm, setAttachForm] = useState(initialAttachForm);
  const [attachSubmitting, setAttachSubmitting] = useState(false);
  const [attachErrors, setAttachErrors] = useState({});
  const [attachFeedback, setAttachFeedback] = useState({ type: "", message: "" });
  const [attachedOffer, setAttachedOffer] = useState(null);
  const [workflowOffer, setWorkflowOffer] = useState(null);

  useEffect(() => {
    let active = true;

    async function loadModules() {
      setModulesLoading(true);

      try {
        const items = await moduleApi.list();

        if (!active) {
          return;
        }

        setModules(items);
      } catch {
        if (active) {
          setModules([]);
        }
      } finally {
        if (active) {
          setModulesLoading(false);
        }
      }
    }

    loadModules();
    setWorkflowOffer(getWorkflowSnapshot().offer);

    return () => {
      active = false;
    };
  }, []);

  function handleOfferChange(event) {
    const { name, type, checked, value } = event.target;

    setOfferForm((current) => ({
      ...current,
      [name]:
        type === "checkbox"
          ? checked
          : name === "code"
            ? normalizeCode(value)
            : value,
    }));

    setOfferErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  function handleAttachChange(event) {
    const { name, type, checked, value } = event.target;

    setAttachForm((current) => ({
      ...current,
      [name]: type === "checkbox" ? checked : value,
    }));

    setAttachErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  async function handleOfferSubmit(event) {
    event.preventDefault();
    setOfferSubmitting(true);
    setOfferErrors({});
    setOfferFeedback({ type: "", message: "" });

    try {
      const offer = await offerApi.create(toPayload(offerForm));
      setCreatedOffer(offer);
      setWorkflowOffer(offer);
      saveWorkflowEntry("offer", offer);
      setAttachForm((current) => ({
        ...current,
        offer_id: String(offer.id),
      }));
      setOfferFeedback({ type: "success", message: "Offre creee avec succes." });
      setOfferForm(initialOfferForm);
    } catch (error) {
      const extracted = extractApiError(error, "La creation de l'offre a echoue.");
      setOfferErrors(extracted.fields);
      setOfferFeedback({ type: "error", message: extracted.message });
    } finally {
      setOfferSubmitting(false);
    }
  }

  async function handleAttachSubmit(event) {
    event.preventDefault();
    setAttachSubmitting(true);
    setAttachErrors({});
    setAttachFeedback({ type: "", message: "" });

    try {
      const offer = await offerApi.attachModule(attachForm.offer_id, {
        module_id: Number(attachForm.module_id),
        is_included: attachForm.is_included,
      });

      setAttachedOffer(offer);
      setWorkflowOffer(offer);
      saveWorkflowEntry("offer", offer);
      setAttachFeedback({ type: "success", message: "Module rattache avec succes." });
    } catch (error) {
      const extracted = extractApiError(error, "Le rattachement du module a echoue.");
      setAttachErrors(extracted.fields);
      setAttachFeedback({ type: "error", message: extracted.message });
    } finally {
      setAttachSubmitting(false);
    }
  }

  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Offers</span>
          <h2>Creer une offre</h2>
          <p>
            Construisez le catalogue commercial avec code, limites d'usage, devise et options de
            publication.
          </p>

          <form className="superadmin-form-grid mt-4" onSubmit={handleOfferSubmit}>
            <label className="superadmin-field">
              <span>Nom</span>
              <input
                name="name"
                className="form-control superadmin-input"
                value={offerForm.name}
                onChange={handleOfferChange}
                placeholder="Starter"
                required
              />
              {offerErrors.name ? <small>{offerErrors.name[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Code</span>
              <input
                name="code"
                className="form-control superadmin-input"
                value={offerForm.code}
                onChange={handleOfferChange}
                placeholder="starter"
                required
              />
              {offerErrors.code ? <small>{offerErrors.code[0]}</small> : null}
            </label>

            <label className="superadmin-field superadmin-field--full">
              <span>Description</span>
              <textarea
                name="description"
                className="form-control superadmin-input superadmin-textarea"
                value={offerForm.description}
                onChange={handleOfferChange}
                placeholder="Description concise de l'offre"
              />
            </label>

            <label className="superadmin-field">
              <span>Prix mensuel</span>
              <input
                name="monthly_price"
                type="number"
                min="0"
                step="0.01"
                className="form-control superadmin-input"
                value={offerForm.monthly_price}
                onChange={handleOfferChange}
                placeholder="49.99"
              />
            </label>

            <label className="superadmin-field">
              <span>Prix annuel</span>
              <input
                name="yearly_price"
                type="number"
                min="0"
                step="0.01"
                className="form-control superadmin-input"
                value={offerForm.yearly_price}
                onChange={handleOfferChange}
                placeholder="499.99"
              />
            </label>

            <label className="superadmin-field">
              <span>Devise</span>
              <select
                name="currency"
                className="form-select superadmin-input"
                value={offerForm.currency}
                onChange={handleOfferChange}
              >
                <option value="MGA">MGA</option>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
              </select>
            </label>

            <label className="superadmin-field">
              <span>Max users</span>
              <input
                name="max_users"
                type="number"
                min="0"
                className="form-control superadmin-input"
                value={offerForm.max_users}
                onChange={handleOfferChange}
                placeholder="10"
              />
            </label>

            <label className="superadmin-field">
              <span>Max branches</span>
              <input
                name="max_branches"
                type="number"
                min="0"
                className="form-control superadmin-input"
                value={offerForm.max_branches}
                onChange={handleOfferChange}
                placeholder="3"
              />
            </label>

            <label className="superadmin-field">
              <span>Max employees</span>
              <input
                name="max_employees"
                type="number"
                min="0"
                className="form-control superadmin-input"
                value={offerForm.max_employees}
                onChange={handleOfferChange}
                placeholder="150"
              />
            </label>

            <label className="superadmin-field">
              <span>Max devices</span>
              <input
                name="max_devices"
                type="number"
                min="0"
                className="form-control superadmin-input"
                value={offerForm.max_devices}
                onChange={handleOfferChange}
                placeholder="8"
              />
            </label>

            <div className="superadmin-field superadmin-field--full">
              <span>Options</span>
              <div className="superadmin-toggle-row">
                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="is_public"
                    checked={offerForm.is_public}
                    onChange={handleOfferChange}
                  />
                  <span>publique</span>
                </label>

                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="is_active"
                    checked={offerForm.is_active}
                    onChange={handleOfferChange}
                  />
                  <span>active</span>
                </label>

                <label className="superadmin-checkbox">
                  <input
                    type="checkbox"
                    name="is_custom"
                    checked={offerForm.is_custom}
                    onChange={handleOfferChange}
                  />
                  <span>custom</span>
                </label>
              </div>
            </div>

            {offerFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  offerFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {offerFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={offerSubmitting}>
                {offerSubmitting ? "Creation..." : "Creer l'offre"}
              </button>
            </div>
          </form>
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Modules</span>
          <h2>Rattacher un module</h2>
          <p>
            Associez un module catalogue a une offre existante pour construire l'offre vendable.
          </p>

          <form className="superadmin-form-grid mt-4" onSubmit={handleAttachSubmit}>
            <label className="superadmin-field">
              <span>ID offre</span>
              <input
                name="offer_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={attachForm.offer_id}
                onChange={handleAttachChange}
                placeholder="1"
                required
              />
            </label>

            <label className="superadmin-field">
              <span>Module</span>
              <select
                name="module_id"
                className="form-select superadmin-input"
                value={attachForm.module_id}
                onChange={handleAttachChange}
                required
                disabled={modulesLoading}
              >
                <option value="">{modulesLoading ? "Chargement..." : "Choisir un module"}</option>
                {modules.map((module) => (
                  <option key={module.id} value={module.id}>
                    {module.name} ({module.code})
                  </option>
                ))}
              </select>
              {attachErrors.module_id ? <small>{attachErrors.module_id[0]}</small> : null}
            </label>

            <div className="superadmin-field superadmin-field--full">
              <span>Relation</span>
              <label className="superadmin-checkbox">
                <input
                  type="checkbox"
                  name="is_included"
                  checked={attachForm.is_included}
                  onChange={handleAttachChange}
                />
                <span>module inclus dans l'offre</span>
              </label>
            </div>

            {attachFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  attachFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {attachFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={attachSubmitting}>
                {attachSubmitting ? "Rattachement..." : "Rattacher le module"}
              </button>
            </div>
          </form>
        </article>
      </section>

      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Derniere offre</span>
          <h2>Resultat de creation</h2>
          {createdOffer ? (
            <dl className="superadmin-definition-list">
              <div>
                <dt>ID</dt>
                <dd>{createdOffer.id}</dd>
              </div>
              <div>
                <dt>Nom</dt>
                <dd>{createdOffer.name}</dd>
              </div>
              <div>
                <dt>Code</dt>
                <dd>{createdOffer.code}</dd>
              </div>
              <div>
                <dt>Mensuel</dt>
                <dd>{createdOffer.monthly_price ?? "-"}</dd>
              </div>
              <div>
                <dt>Annuel</dt>
                <dd>{createdOffer.yearly_price ?? "-"}</dd>
              </div>
              <div>
                <dt>Devise</dt>
                <dd>{createdOffer.currency ?? "-"}</dd>
              </div>
            </dl>
          ) : (
            <p>Aucune offre creee dans cette session.</p>
          )}
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Rattachement</span>
          <h2>Derniere offre mise a jour</h2>
          {workflowOffer ? (
            <div className="superadmin-context-chip mb-4">
              Offre memorisee: #{workflowOffer.id} - {workflowOffer.name}
            </div>
          ) : null}
          {attachedOffer ? (
            <dl className="superadmin-definition-list">
              <div>
                <dt>ID offre</dt>
                <dd>{attachedOffer.id}</dd>
              </div>
              <div>
                <dt>Nom</dt>
                <dd>{attachedOffer.name}</dd>
              </div>
              <div>
                <dt>Code</dt>
                <dd>{attachedOffer.code}</dd>
              </div>
              <div>
                <dt>Modules</dt>
                <dd>{Array.isArray(attachedOffer.modules) ? attachedOffer.modules.length : "-"}</dd>
              </div>
            </dl>
          ) : (
            <p>Aucun module rattache dans cette session.</p>
          )}
        </article>
      </section>
    </div>
  );
}
