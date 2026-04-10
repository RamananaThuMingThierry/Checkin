import { useEffect, useMemo, useState } from "react";
import { attendanceReportApi } from "../../api/axios";
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

function getFileName(contentDisposition) {
  const match = /filename=\"?([^"]+)\"?/i.exec(contentDisposition ?? "");
  return match?.[1] ?? "attendance-report.csv";
}

function getEmployeeName(item) {
  return [item?.employee?.first_name, item?.employee?.last_name].filter(Boolean).join(" ") || "-";
}

function getTypeBadgeClass(type) {
  if (type === "late") {
    return "is-pending";
  }

  if (type === "approved_leave") {
    return "is-approved";
  }

  return "is-rejected";
}

export default function TenantReportingPage() {
  const { user } = useAuth();
  const [filters, setFilters] = useState(() => getDefaultFilters());
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);
  const [refreshKey, setRefreshKey] = useState(0);
  const [feedback, setFeedback] = useState({ type: "", message: "" });
  const [fieldErrors, setFieldErrors] = useState({});

  const tenantEncryptedId = user?.tenant?.encrypted_id ?? "";

  useEffect(() => {
    let active = true;

    async function loadReport() {
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
        const data = await attendanceReportApi.listByTenant(tenantEncryptedId, {
          date_from: filters.date_from,
          date_to: filters.date_to,
          branch_id: filters.branch_id || undefined,
          department_id: filters.department_id || undefined,
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
          "Impossible de charger le rapport des absences et retards."
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

    loadReport();

    return () => {
      active = false;
    };
  }, [filters, refreshKey, tenantEncryptedId]);

  const metrics = useMemo(() => {
    return rows.reduce(
      (accumulator, item) => {
        accumulator.total += 1;

        if (item.type === "late") {
          accumulator.lates += 1;
        } else if (item.type === "approved_leave") {
          accumulator.approvedLeaves += 1;
        } else if (item.type === "absence") {
          accumulator.absences += 1;
        }

        return accumulator;
      },
      { total: 0, lates: 0, absences: 0, approvedLeaves: 0 }
    );
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

  async function handleExport() {
    if (!tenantEncryptedId) {
      setFeedback({
        type: "error",
        message: "Le contexte tenant est introuvable dans la session courante.",
      });
      return;
    }

    setExporting(true);
    setFeedback({ type: "", message: "" });

    try {
      const exported = await attendanceReportApi.exportByTenant(tenantEncryptedId, {
        date_from: filters.date_from,
        date_to: filters.date_to,
        branch_id: filters.branch_id || undefined,
        department_id: filters.department_id || undefined,
      });

      const fileName = getFileName(exported.contentDisposition);
      const objectUrl = window.URL.createObjectURL(exported.blob);
      const anchor = document.createElement("a");

      anchor.href = objectUrl;
      anchor.download = fileName;
      document.body.appendChild(anchor);
      anchor.click();
      anchor.remove();
      window.URL.revokeObjectURL(objectUrl);

      setFeedback({
        type: "success",
        message: `Export CSV genere: ${fileName}`,
      });
    } catch (error) {
      const extracted = extractApiError(error, "L'export CSV a echoue.");
      setFeedback({ type: "error", message: extracted.message });
      setFieldErrors((current) => ({
        ...current,
        ...extracted.fields,
      }));
    } finally {
      setExporting(false);
    }
  }

  return (
    <div className="tenant-page-stack">
      <section className="tenant-page-card">
        <span className="tenant-page-card__eyebrow">Reporting</span>
        <h2>Rapport RH</h2>
        <p>
          Analysez les retards, absences et conges approuves du tenant sur une periode donnee, puis
          exportez le resultat en CSV.
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

      <section className="tenant-metric-grid tenant-reporting-metrics">
        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Total</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.total).padStart(2, "0")}
          </strong>
          <p>Lignes remontees par le rapport sur la periode.</p>
        </article>

        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Retards</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.lates).padStart(2, "0")}
          </strong>
          <p>Presences marquees comme `late`.</p>
        </article>

        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Absences</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.absences).padStart(2, "0")}
          </strong>
          <p>Absences sans couverture par un conge approuve.</p>
        </article>

        <article className="tenant-page-card">
          <span className="tenant-page-card__eyebrow">Conges approuves</span>
          <strong className="tenant-metric-value">
            {loading ? "..." : String(metrics.approvedLeaves).padStart(2, "0")}
          </strong>
          <p>Jours couverts par une demande validee.</p>
        </article>
      </section>

      <section className="tenant-page-card">
        <div className="tenant-leaves-summary">
          <div>
            <span className="tenant-page-card__eyebrow">Resultats</span>
            <h2>Lecture du rapport</h2>
          </div>

          <div className="tenant-reporting-actions">
            <button
              type="button"
              className="btn btn-outline-light"
              onClick={() => setRefreshKey((current) => current + 1)}
              disabled={loading}
            >
              {loading ? "Chargement..." : "Actualiser"}
            </button>

            <button
              type="button"
              className="btn btn-danger"
              onClick={handleExport}
              disabled={exporting}
            >
              {exporting ? "Export..." : "Exporter CSV"}
            </button>
          </div>
        </div>

        <div className="tenant-table-shell mt-4">
          <table className="table align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Code employe</th>
                <th>Employe</th>
                <th>Branche</th>
                <th>Departement</th>
                <th>Retard</th>
                <th>Minutes</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              {!loading && rows.length === 0 ? (
                <tr>
                  <td colSpan="9" className="text-center py-4 text-secondary">
                    Aucun resultat pour la periode et les filtres courants.
                  </td>
                </tr>
              ) : null}

              {rows.map((item, index) => (
                <tr key={`${item.employee_id}-${item.attendance_date}-${item.type}-${index}`}>
                  <td>{item.attendance_date}</td>
                  <td>
                    <span className={`tenant-status-badge ${getTypeBadgeClass(item.type)}`}>
                      {item.type}
                    </span>
                  </td>
                  <td>{item.employee?.employee_code ?? "-"}</td>
                  <td>{getEmployeeName(item)}</td>
                  <td>{item.branch?.name ?? "-"}</td>
                  <td>{item.employee?.department?.name ?? "-"}</td>
                  <td>{item.late_minutes}</td>
                  <td>{item.worked_minutes}</td>
                  <td>{item.status}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </section>
    </div>
  );
}
