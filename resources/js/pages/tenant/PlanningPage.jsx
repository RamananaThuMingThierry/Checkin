import { useEffect, useMemo, useState } from "react";
import { plannedAbsenceApi } from "../../api/axios";
import { useAuth } from "../../hooks/AuthContext";

function getDefaultFilters() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");

  return {
    date_from: `${year}-${month}-01`,
    date_to: `${year}-${month}-${day}`,
    branch_id: "",
    department_id: "",
    employee_id: "",
  };
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

function formatEventTitle(item) {
  if (item.event_type === "holiday") {
    return item.holiday?.name ?? "Jour ferie";
  }

  const employee = item.employee;
  const employeeLabel = [
    employee?.employee_code,
    employee?.first_name,
    employee?.last_name,
  ]
    .filter(Boolean)
    .join(" - ");

  return employeeLabel || "Conge approuve";
}

function formatEventMeta(item) {
  if (item.event_type === "holiday") {
    return item.holiday?.branch?.name
      ? `Branche: ${item.holiday.branch.name}`
      : "Jour ferie global";
  }

  const pieces = [];

  if (item.leave_type?.code) {
    pieces.push(`Type: ${item.leave_type.code}`);
  }

  if (item.employee?.branch?.name) {
    pieces.push(`Branche: ${item.employee.branch.name}`);
  }

  if (item.employee?.department?.name) {
    pieces.push(`Departement: ${item.employee.department.name}`);
  }

  return pieces.join(" | ") || "Conge approuve";
}

function formatDateRange(item) {
  if (item.start_date === item.end_date) {
    return item.start_date;
  }

  return `${item.start_date} -> ${item.end_date}`;
}

export default function TenantPlanningPage() {
  const { user } = useAuth();
  const [filters, setFilters] = useState(() => getDefaultFilters());
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [feedback, setFeedback] = useState({ type: "", message: "" });
  const [fieldErrors, setFieldErrors] = useState({});
  const [refreshKey, setRefreshKey] = useState(0);

  const tenantEncryptedId = user?.tenant?.encrypted_id ?? "";

  useEffect(() => {
    let active = true;

    async function loadCalendar() {
      if (!tenantEncryptedId) {
        if (active) {
          setRows([]);
          setLoading(false);
          setFeedback({
            type: "error",
            message: "Le contexte tenant est introuvable dans la session courante.",
          });
        }
        return;
      }

      setLoading(true);
      setFeedback({ type: "", message: "" });
      setFieldErrors({});

      try {
        const data = await plannedAbsenceApi.listByTenant(tenantEncryptedId, {
          date_from: filters.date_from,
          date_to: filters.date_to,
          branch_id: filters.branch_id || undefined,
          department_id: filters.department_id || undefined,
          employee_id: filters.employee_id.trim() || undefined,
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
          "Impossible de charger le planning des absences."
        );
        setFeedback({ type: "error", message: extracted.message });
        setFieldErrors(extracted.fields);
        setRows([]);
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }

    loadCalendar();

    return () => {
      active = false;
    };
  }, [filters, refreshKey, tenantEncryptedId]);

  const metrics = useMemo(() => {
    const approvedLeaves = rows.filter((item) => item.event_type === "approved_leave").length;
    const holidays = rows.filter((item) => item.event_type === "holiday").length;

    return {
      total: rows.length,
      approvedLeaves,
      holidays,
    };
  }, [rows]);

  function handleChange(event) {
    const { name, value } = event.target;

    setFilters((current) => ({
      ...current,
      [name]: value,
    }));

    setFieldErrors((current) => ({
      ...current,
      [name]: undefined,
    }));
  }

  return (
    <div className="tenant-page-stack">
      <section className="tenant-page-card">
        <span className="tenant-page-card__eyebrow">Planning</span>
        <h2>Absences planifiees</h2>
        <p>
          Affichez les conges approuves et les jours feries sur une seule timeline, avec filtres de
          periode et de perimetre RH.
        </p>

        <div className="tenant-leaves-toolbar mt-4">
          <label className="tenant-leaves-field">
            <span>Date debut</span>
            <input
              name="date_from"
              type="date"
              className="form-control tenant-leaves-input"
              value={filters.date_from}
              onChange={handleChange}
            />
            {fieldErrors.date_from ? <small>{fieldErrors.date_from[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field">
            <span>Date fin</span>
            <input
              name="date_to"
              type="date"
              className="form-control tenant-leaves-input"
              value={filters.date_to}
              onChange={handleChange}
            />
            {fieldErrors.date_to ? <small>{fieldErrors.date_to[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field">
            <span>Branch id</span>
            <input
              name="branch_id"
              type="number"
              className="form-control tenant-leaves-input"
              value={filters.branch_id}
              onChange={handleChange}
              placeholder="Filtre optionnel"
            />
            {fieldErrors.branch_id ? <small>{fieldErrors.branch_id[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field">
            <span>Department id</span>
            <input
              name="department_id"
              type="number"
              className="form-control tenant-leaves-input"
              value={filters.department_id}
              onChange={handleChange}
              placeholder="Filtre optionnel"
            />
            {fieldErrors.department_id ? <small>{fieldErrors.department_id[0]}</small> : null}
          </label>

          <label className="tenant-leaves-field tenant-planning-field--full">
            <span>Employee encrypted id</span>
            <input
              name="employee_id"
              className="form-control tenant-leaves-input"
              value={filters.employee_id}
              onChange={handleChange}
              placeholder="Filtrer un employe precis"
            />
            {fieldErrors.employee_id ? <small>{fieldErrors.employee_id[0]}</small> : null}
          </label>
        </div>

        {feedback.message ? (
          <div
            className={`tenant-inline-alert ${
              feedback.type === "success" ? "is-success" : "is-error"
            }`}
          >
            {feedback.message}
          </div>
        ) : null}
      </section>

      <section className="tenant-metric-grid">
        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Total</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.total).padStart(2, "0")}
          </strong>
          <p>Evenements trouves sur la periode demandee.</p>
        </article>

        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Conges</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.approvedLeaves).padStart(2, "0")}
          </strong>
          <p>Demandes approuvees visibles sur le planning.</p>
        </article>

        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Jours feries</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.holidays).padStart(2, "0")}
          </strong>
          <p>Jours feries globaux ou rattaches a une branche.</p>
        </article>
      </section>

      <section className="tenant-page-card">
        <div className="tenant-leaves-summary">
          <div>
            <span className="tenant-page-card__eyebrow">Timeline</span>
            <h2>Planning courant</h2>
          </div>
          <button
            type="button"
            className="btn btn-outline-light"
            onClick={() => setRefreshKey((current) => current + 1)}
            disabled={loading}
          >
            {loading ? "Chargement..." : "Actualiser"}
          </button>
        </div>

        <div className="tenant-planning-list mt-4">
          {!loading && rows.length === 0 ? (
            <article className="tenant-planning-item">
              <div className="tenant-planning-item__body">
                <strong>Aucun evenement</strong>
                <p>Aucune absence planifiee ni jour ferie ne correspond aux filtres courants.</p>
              </div>
            </article>
          ) : null}

          {rows.map((item) => (
            <article key={`${item.event_type}-${item.id}-${item.start_date}`} className="tenant-planning-item">
              <div className="tenant-planning-item__date">
                <span>{item.event_type === "holiday" ? "Holiday" : "Leave"}</span>
                <strong>{formatDateRange(item)}</strong>
              </div>

              <div className="tenant-planning-item__body">
                <div className="tenant-planning-item__head">
                  <strong>{formatEventTitle(item)}</strong>
                  <span
                    className={`tenant-status-badge ${
                      item.event_type === "holiday" ? "is-holiday" : "is-approved"
                    }`}
                  >
                    {item.event_type}
                  </span>
                </div>
                <p>{formatEventMeta(item)}</p>
              </div>
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}
