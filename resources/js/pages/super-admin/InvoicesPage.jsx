import { useEffect, useState } from "react";
import { invoiceApi, paymentApi } from "../../api/axios";
import { getWorkflowSnapshot, saveWorkflowEntry } from "../../utils/workflowStore";

const initialGenerateForm = {
  subscription_id: "",
  due_date: "",
  notes: "",
};

const initialListForm = {
  tenant_id: "",
  status: "",
};

const initialPaymentForm = {
  invoice_id: "",
  amount: "",
  currency: "MGA",
  payment_date: "",
  reference: "",
  transaction_id: "",
  notes: "",
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

export default function SuperAdminInvoicesPage() {
  const [generateForm, setGenerateForm] = useState(initialGenerateForm);
  const [generateSubmitting, setGenerateSubmitting] = useState(false);
  const [generateErrors, setGenerateErrors] = useState({});
  const [generateFeedback, setGenerateFeedback] = useState({ type: "", message: "" });
  const [generatedInvoice, setGeneratedInvoice] = useState(null);

  const [listForm, setListForm] = useState(initialListForm);
  const [listSubmitting, setListSubmitting] = useState(false);
  const [listErrors, setListErrors] = useState({});
  const [listFeedback, setListFeedback] = useState({ type: "", message: "" });
  const [tenantInvoices, setTenantInvoices] = useState([]);

  const [paymentForm, setPaymentForm] = useState(initialPaymentForm);
  const [paymentSubmitting, setPaymentSubmitting] = useState(false);
  const [paymentErrors, setPaymentErrors] = useState({});
  const [paymentFeedback, setPaymentFeedback] = useState({ type: "", message: "" });
  const [recordedPayment, setRecordedPayment] = useState(null);
  const [workflowContext, setWorkflowContext] = useState(getWorkflowSnapshot);

  useEffect(() => {
    const snapshot = getWorkflowSnapshot();
    setWorkflowContext(snapshot);
    setGenerateForm((current) => ({
      ...current,
      subscription_id: current.subscription_id || String(snapshot.subscription?.id ?? ""),
    }));
    setListForm((current) => ({
      ...current,
      tenant_id: current.tenant_id || String(snapshot.tenant?.id ?? ""),
    }));
    setPaymentForm((current) => ({
      ...current,
      invoice_id: current.invoice_id || String(snapshot.invoice?.id ?? ""),
    }));
  }, []);

  function handleGenerateChange(event) {
    const { name, value } = event.target;

    setGenerateForm((current) => ({
      ...current,
      [name]: value,
    }));

    setGenerateErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  function handleListChange(event) {
    const { name, value } = event.target;

    setListForm((current) => ({
      ...current,
      [name]: value,
    }));

    setListErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  function handlePaymentChange(event) {
    const { name, value } = event.target;

    setPaymentForm((current) => ({
      ...current,
      [name]: value,
    }));

    setPaymentErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  async function handleGenerateSubmit(event) {
    event.preventDefault();
    setGenerateSubmitting(true);
    setGenerateErrors({});
    setGenerateFeedback({ type: "", message: "" });

    try {
      const invoice = await invoiceApi.generate(generateForm.subscription_id, buildPayload(generateForm));
      setGeneratedInvoice(invoice);
      setWorkflowContext(saveWorkflowEntry("invoice", invoice));
      setPaymentForm((current) => ({
        ...current,
        invoice_id: String(invoice.id),
        currency: invoice.currency ?? current.currency,
      }));
      setGenerateFeedback({ type: "success", message: "Facture generee avec succes." });
    } catch (error) {
      const extracted = extractApiError(error, "La generation de facture a echoue.");
      setGenerateErrors(extracted.fields);
      setGenerateFeedback({ type: "error", message: extracted.message });
    } finally {
      setGenerateSubmitting(false);
    }
  }

  async function handleListSubmit(event) {
    event.preventDefault();
    setListSubmitting(true);
    setListErrors({});
    setListFeedback({ type: "", message: "" });

    try {
      const invoices = await invoiceApi.listByTenant(listForm.tenant_id, {
        status: listForm.status || undefined,
      });
      setTenantInvoices(invoices);
      setListFeedback({
        type: "success",
        message: `${invoices.length} facture(s) chargee(s).`,
      });
    } catch (error) {
      const extracted = extractApiError(error, "Le chargement des factures a echoue.");
      setListErrors(extracted.fields);
      setListFeedback({ type: "error", message: extracted.message });
    } finally {
      setListSubmitting(false);
    }
  }

  async function handlePaymentSubmit(event) {
    event.preventDefault();
    setPaymentSubmitting(true);
    setPaymentErrors({});
    setPaymentFeedback({ type: "", message: "" });

    try {
      const payment = await paymentApi.create(paymentForm.invoice_id, buildPayload(paymentForm));
      setRecordedPayment(payment);
      setPaymentFeedback({ type: "success", message: "Paiement enregistre avec succes." });
    } catch (error) {
      const extracted = extractApiError(error, "L'enregistrement du paiement a echoue.");
      setPaymentErrors(extracted.fields);
      setPaymentFeedback({ type: "error", message: extracted.message });
    } finally {
      setPaymentSubmitting(false);
    }
  }

  return (
    <div className="superadmin-page-stack">
      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Generation</span>
          <h2>Generer une facture</h2>
          <p>
            Lancez la facturation a partir d'un abonnement actif en utilisant l'ID subscription.
          </p>
          {workflowContext.subscription ? (
            <div className="superadmin-context-chip mt-4">
              Abonnement memorise: #{workflowContext.subscription.id}
            </div>
          ) : null}

          <form className="superadmin-form-grid mt-4" onSubmit={handleGenerateSubmit}>
            <label className="superadmin-field">
              <span>ID abonnement</span>
              <input
                name="subscription_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={generateForm.subscription_id}
                onChange={handleGenerateChange}
                placeholder="1"
                required
              />
              {generateErrors.subscription_id ? <small>{generateErrors.subscription_id[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Date d'echeance</span>
              <input
                name="due_date"
                type="date"
                className="form-control superadmin-input"
                value={generateForm.due_date}
                onChange={handleGenerateChange}
              />
              {generateErrors.due_date ? <small>{generateErrors.due_date[0]}</small> : null}
            </label>

            <label className="superadmin-field superadmin-field--full">
              <span>Notes</span>
              <textarea
                name="notes"
                className="form-control superadmin-input superadmin-textarea"
                value={generateForm.notes}
                onChange={handleGenerateChange}
                placeholder="Commentaire ou contexte de facturation"
              />
            </label>

            {generateFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  generateFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {generateFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={generateSubmitting}>
                {generateSubmitting ? "Generation..." : "Generer la facture"}
              </button>
            </div>
          </form>
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Listing</span>
          <h2>Consulter les factures d'un tenant</h2>
          <p>Chargez l'historique d'un tenant avec un filtre de statut optionnel.</p>
          {workflowContext.tenant ? (
            <div className="superadmin-context-chip mt-4">
              Tenant memorise: #{workflowContext.tenant.id} - {workflowContext.tenant.name}
            </div>
          ) : null}

          <form className="superadmin-form-grid mt-4" onSubmit={handleListSubmit}>
            <label className="superadmin-field">
              <span>ID tenant</span>
              <input
                name="tenant_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={listForm.tenant_id}
                onChange={handleListChange}
                placeholder="1"
                required
              />
              {listErrors.tenant_id ? <small>{listErrors.tenant_id[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Statut</span>
              <select
                name="status"
                className="form-select superadmin-input"
                value={listForm.status}
                onChange={handleListChange}
              >
                <option value="">Tous</option>
                <option value="draft">draft</option>
                <option value="issued">issued</option>
                <option value="partially_paid">partially_paid</option>
                <option value="paid">paid</option>
                <option value="overdue">overdue</option>
                <option value="cancelled">cancelled</option>
                <option value="refunded">refunded</option>
              </select>
            </label>

            {listFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  listFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {listFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={listSubmitting}>
                {listSubmitting ? "Chargement..." : "Charger les factures"}
              </button>
            </div>
          </form>
        </article>
      </section>

      <section className="superadmin-detail-grid">
        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Paiement</span>
          <h2>Enregistrer un paiement</h2>
          <p>Associez un paiement a une facture existante en respectant la devise de la facture.</p>
          {workflowContext.invoice ? (
            <div className="superadmin-context-chip mt-4">
              Facture memorisee: #{workflowContext.invoice.id}
            </div>
          ) : null}

          <form className="superadmin-form-grid mt-4" onSubmit={handlePaymentSubmit}>
            <label className="superadmin-field">
              <span>ID facture</span>
              <input
                name="invoice_id"
                type="number"
                min="1"
                className="form-control superadmin-input"
                value={paymentForm.invoice_id}
                onChange={handlePaymentChange}
                placeholder="1"
                required
              />
            </label>

            <label className="superadmin-field">
              <span>Montant</span>
              <input
                name="amount"
                type="number"
                min="0.01"
                step="0.01"
                className="form-control superadmin-input"
                value={paymentForm.amount}
                onChange={handlePaymentChange}
                placeholder="10000"
                required
              />
              {paymentErrors.amount ? <small>{paymentErrors.amount[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Devise</span>
              <select
                name="currency"
                className="form-select superadmin-input"
                value={paymentForm.currency}
                onChange={handlePaymentChange}
              >
                <option value="MGA">MGA</option>
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
              </select>
              {paymentErrors.currency ? <small>{paymentErrors.currency[0]}</small> : null}
            </label>

            <label className="superadmin-field">
              <span>Date de paiement</span>
              <input
                name="payment_date"
                type="date"
                className="form-control superadmin-input"
                value={paymentForm.payment_date}
                onChange={handlePaymentChange}
              />
            </label>

            <label className="superadmin-field">
              <span>Reference</span>
              <input
                name="reference"
                className="form-control superadmin-input"
                value={paymentForm.reference}
                onChange={handlePaymentChange}
                placeholder="PAY-REF-001"
              />
            </label>

            <label className="superadmin-field">
              <span>Transaction ID</span>
              <input
                name="transaction_id"
                className="form-control superadmin-input"
                value={paymentForm.transaction_id}
                onChange={handlePaymentChange}
                placeholder="TXN-001"
              />
            </label>

            <label className="superadmin-field superadmin-field--full">
              <span>Notes</span>
              <textarea
                name="notes"
                className="form-control superadmin-input superadmin-textarea"
                value={paymentForm.notes}
                onChange={handlePaymentChange}
                placeholder="Commentaire de rapprochement"
              />
            </label>

            {paymentFeedback.message ? (
              <div
                className={`superadmin-inline-alert ${
                  paymentFeedback.type === "success" ? "is-success" : "is-error"
                }`}
              >
                {paymentFeedback.message}
              </div>
            ) : null}

            <div className="superadmin-form-actions">
              <button type="submit" className="btn btn-danger" disabled={paymentSubmitting}>
                {paymentSubmitting ? "Enregistrement..." : "Enregistrer le paiement"}
              </button>
            </div>
          </form>
        </article>

        <article className="superadmin-page-card">
          <span className="superadmin-page-card__eyebrow">Resultats</span>
          <h2>Billing courant</h2>

          {generatedInvoice ? (
            <dl className="superadmin-definition-list">
              <div>
                <dt>Derniere facture</dt>
                <dd>#{generatedInvoice.id}</dd>
              </div>
              <div>
                <dt>Statut</dt>
                <dd>{generatedInvoice.status}</dd>
              </div>
              <div>
                <dt>Total</dt>
                <dd>{generatedInvoice.total}</dd>
              </div>
              <div>
                <dt>Solde</dt>
                <dd>{generatedInvoice.balance_due}</dd>
              </div>
            </dl>
          ) : null}

          {recordedPayment ? (
            <dl className="superadmin-definition-list mt-4">
              <div>
                <dt>Dernier paiement</dt>
                <dd>#{recordedPayment.id}</dd>
              </div>
              <div>
                <dt>Facture</dt>
                <dd>{recordedPayment.invoice_id}</dd>
              </div>
              <div>
                <dt>Montant</dt>
                <dd>{recordedPayment.amount}</dd>
              </div>
              <div>
                <dt>Statut</dt>
                <dd>{recordedPayment.status}</dd>
              </div>
            </dl>
          ) : null}

          {!generatedInvoice && !recordedPayment ? (
            <p>Aucune operation de billing effectuee dans cette session.</p>
          ) : null}
        </article>
      </section>

      <section className="superadmin-page-card">
        <span className="superadmin-page-card__eyebrow">Liste des factures</span>
        <h2>Factures du tenant</h2>
        {tenantInvoices.length ? (
          <div className="superadmin-table-shell">
            <table className="table table-dark align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Numero</th>
                  <th>Statut</th>
                  <th>Total</th>
                  <th>Solde</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                {tenantInvoices.map((invoice) => (
                  <tr key={invoice.id}>
                    <td>{invoice.id}</td>
                    <td>{invoice.invoice_number}</td>
                    <td>{invoice.status}</td>
                    <td>{invoice.total}</td>
                    <td>{invoice.balance_due}</td>
                    <td>{invoice.invoice_date}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <p>Aucune facture chargee pour le moment.</p>
        )}
      </section>
    </div>
  );
}
