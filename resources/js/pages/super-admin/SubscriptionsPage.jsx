import { useEffect, useState } from "react";
import { subscriptionApi } from "../../api/axios";
import { getWorkflowSnapshot, saveWorkflowEntry } from "../../utils/workflowStore";

const initialSubscriptionForm = {
  tenant_id: "",
  offer_id: "",
  billing_cycle: "monthly",
  status: "trial",
  starts_at: "",
  ends_at: "",
  next_billing_date: "",
  trial_ends_at: "",
  notes: "",
};

const initialActivationForm = {
  subscription_id: "",
};

function buildPayload(form) {
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

export default function SuperAdminSubscriptionsPage() {
  const [subscriptionForm, setSubscriptionForm] = useState(initialSubscriptionForm);
  const [subscriptionSubmitting, setSubscriptionSubmitting] = useState(false);
  const [subscriptionErrors, setSubscriptionErrors] = useState({});
  const [subscriptionFeedback, setSubscriptionFeedback] = useState({ type: "", message: "" });
  const [createdSubscription, setCreatedSubscription] = useState(null);

  const [activationForm, setActivationForm] = useState(initialActivationForm);
  const [activationSubmitting, setActivationSubmitting] = useState(false);
  const [activationErrors, setActivationErrors] = useState({});
  const [activationFeedback, setActivationFeedback] = useState({ type: "", message: "" });
  const [activatedModules, setActivatedModules] = useState([]);
  const [workflowContext, setWorkflowContext] = useState(getWorkflowSnapshot);

  useEffect(() => {
    const snapshot = getWorkflowSnapshot();
    setWorkflowContext(snapshot);
    setSubscriptionForm((current) => ({
      ...current,
      tenant_id: current.tenant_id || String(snapshot.tenant?.id ?? ""),
      offer_id: current.offer_id || String(snapshot.offer?.id ?? ""),
    }));
    setActivationForm((current) => ({
      ...current,
      subscription_id: current.subscription_id || String(snapshot.subscription?.id ?? ""),
    }));
  }, []);

  function handleSubscriptionChange(event) {
    const { name, value } = event.target;

    setSubscriptionForm((current) => ({
      ...current,
      [name]: value,
    }));

    setSubscriptionErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  function handleActivationChange(event) {
    const { name, value } = event.target;

    setActivationForm((current) => ({
      ...current,
      [name]: value,
    }));

    setActivationErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  async function handleSubscriptionSubmit(event) {
    event.preventDefault();
    setSubscriptionSubmitting(true);
    setSubscriptionErrors({});
    setSubscriptionFeedback({ type: "", message: "" });

    try {
      const subscription = await subscriptionApi.create({
        ...buildPayload(subscriptionForm),
        tenant_id: Number(subscriptionForm.tenant_id),
        offer_id: Number(subscriptionForm.offer_id),
      });

      setCreatedSubscription(subscription);
      setWorkflowContext(saveWorkflowEntry("subscription", subscription));
      setActivationForm({ subscription_id: String(subscription.id) });
      setSubscriptionFeedback({
        type: "success",
        message: "Abonnement cree avec succes.",
      });
      setSubscriptionForm(initialSubscriptionForm);
    } catch (error) {
      const extracted = extractApiError(error, "La creation de l'abonnement a echoue.");
      setSubscriptionErrors(extracted.fields);
      setSubscriptionFeedback({ type: "error", message: extracted.message });
    } finally {
      setSubscriptionSubmitting(false);
    }
  }

  async function handleActivationSubmit(event) {
    event.preventDefault();
    setActivationSubmitting(true);
    setActivationErrors({});
    setActivationFeedback({ type: "", message: "" });

    try {
      const modules = await subscriptionApi.activateModules(activationForm.subscription_id);
      setActivatedModules(Array.isArray(modules) ? modules : []);
      setActivationFeedback({
        type: "success",
        message: "Modules du tenant actives avec succes.",
      });
    } catch (error) {
      const extracted = extractApiError(error, "L'activation des modules a echoue.");
      setActivationErrors(extracted.fields);
      setActivationFeedback({ type: "error", message: extracted.message });
    } finally {
      setActivationSubmitting(false);
    }
  }

  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Subscriptions</span>
          <h2>Souscrire une offre</h2>
          <p>
            Creez un abonnement a partir d'un tenant et d'une offre existants. Les IDs backend sont
            utilises ici tant que les listes de selection ne sont pas encore exposees.
          </p>
          {workflowContext.tenant || workflowContext.offer ? (
            <div className="superadmin-context-chip mt-4">
              {workflowContext.tenant ? `Tenant #${workflowContext.tenant.id}` : "Tenant non memorise"}
              {" / "}
              {workflowContext.offer ? `Offre #${workflowContext.offer.id}` : "Offre non memorisee"}
            </div>
          ) : null}

          <form className="superadmin-form-grid mt-4" onSubmit={handleSubscriptionSubmit}>
            <label className="superadmin-field">
              <span>ID tenant</span>
              <input
                name="tenant_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={subscriptionForm.tenant_id}
                onChange={handleSubscriptionChange}
                placeholder="1"
                required
              />
              {subscriptionErrors.tenant_id ? <small>{subscriptionErrors.tenant_id[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>ID offre</span>
              <input
                name="offer_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={subscriptionForm.offer_id}
                onChange={handleSubscriptionChange}
                placeholder="1"
                required
              />
              {subscriptionErrors.offer_id ? <small>{subscriptionErrors.offer_id[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Cycle de facturation</span>
              <select
                name="billing_cycle"
                className="form-select superadmin-input"
                value={subscriptionForm.billing_cycle}
                onChange={handleSubscriptionChange}
              >
                <option value="monthly">monthly</option>
                <option value="quarterly">quarterly</option>
                <option value="semiannual">semiannual</option>
                <option value="yearly">yearly</option>
              </select>
            </label>

            <label className="superadmin-field">
              <span>Statut</span>
              <select
                name="status"
                className="form-select superadmin-input"
                value={subscriptionForm.status}
                onChange={handleSubscriptionChange}
              >
                <option value="trial">trial</option>
                <option value="active">active</option>
                <option value="past_due">past_due</option>
                <option value="unpaid">unpaid</option>
                <option value="cancelled">cancelled</option>
                <option value="expired">expired</option>
                <option value="suspended">suspended</option>
              </select>
            </label>

            <label className="superadmin-field">
              <span>Date de debut</span>
              <input
                name="starts_at"
                type="date"
                className="form-control superadmin-input"
                value={subscriptionForm.starts_at}
                onChange={handleSubscriptionChange}
                required
              />
              {subscriptionErrors.starts_at ? <small>{subscriptionErrors.starts_at[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Date de fin</span>
              <input
                name="ends_at"
                type="date"
                className="form-control superadmin-input"
                value={subscriptionForm.ends_at}
                onChange={handleSubscriptionChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Fin de trial</span>
              <input
                name="trial_ends_at"
                type="date"
                className="form-control superadmin-input"
                value={subscriptionForm.trial_ends_at}
                onChange={handleSubscriptionChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Prochaine facturation</span>
              <input
                name="next_billing_date"
                type="date"
                className="form-control superadmin-input"
                value={subscriptionForm.next_billing_date}
                onChange={handleSubscriptionChange}
              />
            </label>

            <label className="superadmin-field superadmin-field--full">
              <span>Notes</span>
              <textarea
                name="notes"
                className="form-control superadmin-input superadmin-textarea"
                value={subscriptionForm.notes}
                onChange={handleSubscriptionChange}
                placeholder="Contexte commercial ou remarque interne"
              />
            </label>

            {subscriptionFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  subscriptionFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {subscriptionFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={subscriptionSubmitting}>
                {subscriptionSubmitting ? "Creation..." : "Creer l'abonnement"}
              </button>
            </div>
          </form>
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Activation</span>
          <h2>Activer les modules du tenant</h2>
          <p>
            Declenchez l'activation des modules inclus dans l'offre a partir de l'ID abonnement.
          </p>
          {workflowContext.subscription ? (
            <div className="superadmin-context-chip mt-4">
              Abonnement memorise: #{workflowContext.subscription.id}
            </div>
          ) : null}

          <form className="superadmin-form-grid mt-4" onSubmit={handleActivationSubmit}>
            <label className="superadmin-field">
              <span>ID abonnement</span>
              <input
                name="subscription_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={activationForm.subscription_id}
                onChange={handleActivationChange}
                placeholder="1"
                required
              />
              {activationErrors.subscription_id ? <small>{activationErrors.subscription_id[0]}</small> : null}
            </label>

            {activationFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  activationFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {activationFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={activationSubmitting}>
                {activationSubmitting ? "Activation..." : "Activer les modules"}
              </button>
            </div>
          </form>
        </article>
      </section>

      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Dernier abonnement</span>
          <h2>Resultat de souscription</h2>
          {createdSubscription ? (
            <dl className="superadmin-definition-list">
              <div>
                <dt>ID</dt>
                <dd>{createdSubscription.id}</dd>
              </div>
              <div>
                <dt>Tenant</dt>
                <dd>{createdSubscription.tenant_id}</dd>
              </div>
              <div>
                <dt>Offer</dt>
                <dd>{createdSubscription.offer_id}</dd>
              </div>
              <div>
                <dt>Cycle</dt>
                <dd>{createdSubscription.billing_cycle}</dd>
              </div>
              <div>
                <dt>Montant de base</dt>
                <dd>{createdSubscription.base_amount}</dd>
              </div>
              <div>
                <dt>Total</dt>
                <dd>{createdSubscription.total_amount}</dd>
              </div>
            </dl>
          ) : (
            <p>Aucun abonnement cree dans cette session.</p>
          )}
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Modules actives</span>
          <h2>Resultat de l'activation</h2>
          {activatedModules.length ? (
            <div className="superadmin-list-grid">
              {activatedModules.map((item) => (
                <article key={item.id} className="superadmin-list-card">
                  <strong>Module #{item.module_id}</strong>
                  <p>Tenant #{item.tenant_id}</p>
                  <p>Actif: {item.is_enabled ? "oui" : "non"}</p>
                </article>
              ))}
            </div>
          ) : (
            <p>Aucune activation effectuee dans cette session.</p>
          )}
        </article>
      </section>
    </div>
  );
}
