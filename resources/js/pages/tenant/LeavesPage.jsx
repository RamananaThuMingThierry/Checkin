import { useEffect, useState } from "react";
import { leaveRequestApi } from "../../api/axios";
import { useAuth } from "../../hooks/AuthContext";

const initialFilters = {
  status: "pending",
  date_from: "",
  date_to: "",
  employee_id: "",
};

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

function formatDateRange(item) {
  if (!item?.start_date && !item?.end_date) {
    return "-";
  }

  if (item?.start_date === item?.end_date) {
    return item.start_date;
  }

  return `${item.start_date} -> ${item.end_date}`;
}

function getEmployeeLabel(item) {
  const employee = item?.employee;

  if (!employee) {
    return "Employe inconnu";
  }

  return [employee.employee_code, employee.first_name, employee.last_name].filter(Boolean).join(" - ");
}

function getStatusBadgeClass(status) {
  if (status === "approved") {
    return "is-approved";
  }

  if (status === "rejected") {
    return "is-rejected";
  }

  if (status === "cancelled") {
    return "is-cancelled";
  }

  return "is-pending";
}

export default function TenantLeavesPage() {
  const { user } = useAuth();
  const [filters, setFilters] = useState(initialFilters);
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshKey, setRefreshKey] = useState(0);
  const [listFeedback, setListFeedback] = useState({ type: "", message: "" });
  const [filterErrors, setFilterErrors] = useState({});
  const [actionFeedback, setActionFeedback] = useState({ type: "", message: "" });
  const [actionErrors, setActionErrors] = useState({});
  const [busyId, setBusyId] = useState("");
  const [rejectingId, setRejectingId] = useState("");
  const [rejectionReason, setRejectionReason] = useState("");

  const tenantEncryptedId = user?.tenant?.encrypted_id ?? "";

  useEffect(() => {
    let active = true;

    async function loadLeaveRequests() {
      if (!tenantEncryptedId) {
        if (active) {
          setRows([]);
          setLoading(false);
          setListFeedback({
            type: "error",
            message: "Le contexte tenant est introuvable dans la session courante.",
          });
        }
        return;
      }

      setLoading(true);
      setListFeedback({ type: "", message: "" });
      setFilterErrors({});

      try {
        const data = await leaveRequestApi.listByTenant(tenantEncryptedId, {
          ...filters,
          employee_id: filters.employee_id.trim(),
        });

        if (!active) {
          return;
        }

        setRows(Array.isArray(data) ? data : []);
      } catch (error) {
        if (!active) {
          return;
        }

        const extracted = extractApiError(
          error,
          "Impossible de charger les demandes de conge pour ce tenant."
        );
        setListFeedback({ type: "error", message: extracted.message });
        setFilterErrors(extracted.fields);
        setRows([]);
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }

    loadLeaveRequests();

    return () => {
      active = false;
    };
  }, [filters, refreshKey, tenantEncryptedId]);

  function handleFilterChange(event) {
    const { name, value } = event.target;

    setFilters((current) => ({
      ...current,
      [name]: value,
    }));

    setFilterErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  async function handleApprove(item) {
    setBusyId(item.encrypted_id);
    setActionFeedback({ type: "", message: "" });
    setActionErrors({});

    try {
      await leaveRequestApi.approve(item.encrypted_id);
      setActionFeedback({
        type: "success",
        message: `La demande de ${getEmployeeLabel(item)} a ete approuvee.`,
      });
      setRejectingId("");
      setRejectionReason("");
      setRefreshKey((current) => current + 1);
    } catch (error) {
      const extracted = extractApiError(
        error,
        "L'approbation de la demande a echoue."
      );
      setActionFeedback({ type: "error", message: extracted.message });
      setActionErrors(extracted.fields);
    } finally {
      setBusyId("");
    }
  }

  async function handleReject(item) {
    setBusyId(item.encrypted_id);
    setActionFeedback({ type: "", message: "" });
    setActionErrors({});

    try {
      await leaveRequestApi.reject(item.encrypted_id, {
        rejection_reason: rejectionReason,
      });
      setActionFeedback({
        type: "success",
        message: `La demande de ${getEmployeeLabel(item)} a ete rejetee.`,
      });
      setRejectingId("");
      setRejectionReason("");
      setRefreshKey((current) => current + 1);
    } catch (error) {
      const extracted = extractApiError(error, "Le rejet de la demande a echoue.");
      setActionFeedback({ type: "error", message: extracted.message });
      setActionErrors(extracted.fields);
    } finally {
      setBusyId("");
    }
  }

  return (
    <div className="tenant-page-stack">
      <section className="tenant-page-card">
        <span className="tenant-page-card__eyebrow">Leaves</span>
        <h2>Demandes de conge</h2>
        <p>
          Listez les demandes du tenant, filtrez la periode et traitez les demandes en attente
          avec approbation ou rejet motive.
        </p>

        <div className="tenant-leaves-toolbar mt-4">
          <label className="tenant-leaves-field">
            <span>Statut</span>
            <select
              name="status"
              className="form-select tenant-leaves-input"
              value={filters.status}
              onChange={handleFilterChange}
            >
              <option value="">Tous</option>
              <option value="pending">pending</option>
              <option value="approved">approved</option>
              <option value="rejected">rejected</option>
              <option value="cancelled">cancelled</option>
            </select>
            {filterErrors.status ? <small>{filterErrors.status[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field">
            <span>Date debut</span>
            <input
              name="date_from"
              type="date"
              className="form-control tenant-leaves-input"
              value={filters.date_from}
              onChange={handleFilterChange}
            />
            {filterErrors.date_from ? <small>{filterErrors.date_from[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field">
            <span>Date fin</span>
            <input
              name="date_to"
              type="date"
              className="form-control tenant-leaves-input"
              value={filters.date_to}
              onChange={handleFilterChange}
            />
            {filterErrors.date_to ? <small>{filterErrors.date_to[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field tenant-leaves-field--wide">
            <span>Employee encrypted id</span>
            <input
              name="employee_id"
              className="form-control tenant-leaves-input"
              value={filters.employee_id}
              onChange={handleFilterChange}
              placeholder="Filtrer un employe precis"
            />
            {filterErrors.employee_id ? <small>{filterErrors.employee_id[0]}</small> : null}
          </label>
        </div>

        {listFeedback.message ? (
          <div
            className={`tenant-inline-alert ${
              listFeedback.type === "success" ? "is-success" : "is-error"
            }`}
          >
            {listFeedback.message}
          </div>
        ) : null}

        {actionFeedback.message ? (
          <div
            className={`tenant-inline-alert ${
              actionFeedback.type === "success" ? "is-success" : "is-error"
            }`}
          >
            {actionFeedback.message}
          </div>
        ) : null}
      </section>

      <section className="tenant-page-card">
        <div className="tenant-leaves-summary">
          <div>
            <span className="tenant-page-card__eyebrow">Traitement</span>
            <h2>File des demandes</h2>
          </div>
          <strong>{loading ? "Chargement..." : `${rows.length} demande(s)`}</strong>
        </div>

        <div className="tenant-table-shell mt-4">
          <table className="table align-middle">
            <thead>
              <tr>
                <th>Employe</th>
                <th>Type</th>
                <th>Periode</th>
                <th>Jours</th>
                <th>Statut</th>
                <th>Motif</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {!loading && rows.length === 0 ? (
                <tr>
                  <td colSpan="7" className="text-center py-4 text-secondary">
                    Aucune demande trouvee avec les filtres courants.
                  </td>
                </tr>
              ) : null}

              {rows.map((item) => {
                const isPending = item.status === "pending";
                const isBusy = busyId === item.encrypted_id;
                const isRejecting = rejectingId === item.encrypted_id;

                return (
                  <tr key={item.encrypted_id}>
                    <td>
                      <strong>{getEmployeeLabel(item)}</strong>
                    </td>
                    <td>{item.leave_type?.code ?? "-"}</td>
                    <td>{formatDateRange(item)}</td>
                    <td>{item.days_count}</td>
                    <td>
                      <span className={`tenant-status-badge ${getStatusBadgeClass(item.status)}`}>
                        {item.status}
                      </span>
                    </td>
                    <td>
                      <div className="tenant-leaves-reason">
                        <p>{item.reason || "-"}</p>
                        {item.rejection_reason ? (
                          <small>Rejet: {item.rejection_reason}</small>
                        ) : null}
                      </div>
                    </td>
                    <td>
                      {isPending ? (
                        <div className="tenant-leaves-actions">
                          <button
                            type="button"
                            className="btn btn-danger btn-sm"
                            onClick={() => handleApprove(item)}
                            disabled={isBusy}
                          >
                            {isBusy ? "Traitement..." : "Approuver"}
                          </button>

                          <button
                            type="button"
                            className="btn btn-outline-light btn-sm"
                            onClick={() => {
                              setActionFeedback({ type: "", message: "" });
                              setActionErrors({});
                              setRejectingId(isRejecting ? "" : item.encrypted_id);
                              setRejectionReason(isRejecting ? "" : "");
                            }}
                            disabled={isBusy}
                          >
                            Rejeter
                          </button>

                          {isRejecting ? (
                            <div className="tenant-reject-box">
                              <textarea
                                className="form-control tenant-leaves-input"
                                value={rejectionReason}
                                onChange={(event) => {
                                  setRejectionReason(event.target.value);
                                  setActionErrors((current) => ({
                                    ...current,
                                    rejection_reason: undefined,
                                  }));
                                }}
                                placeholder="Motif obligatoire du rejet"
                              />
                              {actionErrors.rejection_reason ? (
                                <small>{actionErrors.rejection_reason[0]}</small>
                              ) : null}
                              {actionErrors.status ? <small>{actionErrors.status[0]}</small> : null}
                              {actionErrors.leave_request ? (
                                <small>{actionErrors.leave_request[0]}</small>
                              ) : null}
                              <div className="tenant-reject-box__actions">
                                <button
                                  type="button"
                                  className="btn btn-danger btn-sm"
                                  onClick={() => handleReject(item)}
                                  disabled={isBusy}
                                >
                                  {isBusy ? "Traitement..." : "Confirmer le rejet"}
                                </button>
                                <button
                                  type="button"
                                  className="btn btn-link btn-sm text-light"
                                  onClick={() => {
                                    setRejectingId("");
                                    setRejectionReason("");
                                    setActionErrors({});
                                  }}
                                  disabled={isBusy}
                                >
                                  Annuler
                                </button>
                              </div>
                            </div>
                          ) : null}
                        </div>
                      ) : (
                        <span className="text-secondary">Aucune action</span>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </section>
    </div>
  );
}
