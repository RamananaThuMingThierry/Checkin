import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../hooks/AuthContext";
import { getDefaultRouteForUser } from "../../utils/auth";

function resolveErrorMessage(error) {
  const validationErrors = error?.response?.data?.errors;

  if (validationErrors && typeof validationErrors === "object") {
    const firstEntry = Object.values(validationErrors)[0];

    if (Array.isArray(firstEntry) && firstEntry[0]) {
      return firstEntry[0];
    }
  }

  return error?.response?.data?.message ?? "Connexion impossible. Verifiez vos identifiants.";
}

export default function Login() {
  const navigate = useNavigate();
  const { login } = useAuth();

  const [form, setForm] = useState({
    email: "",
    password: "",
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setSubmitting(true);
    setError("");

    try {
      const authenticatedUser = await login(form);
      navigate(getDefaultRouteForUser(authenticatedUser), { replace: true });
    } catch (submitError) {
      setError(resolveErrorMessage(submitError));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="login-screen">
      <div className="login-screen__aurora login-screen__aurora--left" />
      <div className="login-screen__aurora login-screen__aurora--right" />

      <div className="container py-4 py-lg-5">
        <div className="row min-vh-100 align-items-center justify-content-center">
          <div className="col-12">
            <div className="login-card shadow-lg">
              <div className="row g-0">
                <div className="col-lg-6">
                  <section className="login-brand-panel h-100">
                    <div className="login-badge">Pointages SaaS</div>
                    <h1 className="login-title">
                      Controlez les presences depuis une interface nette, sobre et rapide.
                    </h1>
                    <p className="login-copy">
                      Connectez-vous pour acceder a votre espace de suivi RH, de consolidation des
                      pointages et d'administration quotidienne.
                    </p>

                    <div className="login-feature-list">
                      <div className="login-feature">
                        <span className="login-feature__index">01</span>
                        <div>
                          <strong>Connexion centralisee</strong>
                          <p>Un seul point d'entree pour les profils plateforme et tenant.</p>
                        </div>
                      </div>

                      <div className="login-feature">
                        <span className="login-feature__index">02</span>
                        <div>
                          <strong>Session persistante</strong>
                          <p>Le token local est recharge automatiquement a l'ouverture.</p>
                        </div>
                      </div>

                      <div className="login-feature">
                        <span className="login-feature__index">03</span>
                        <div>
                          <strong>Base API-first</strong>
                          <p>Le frontend reste decouple du backend Laravel versionne.</p>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>

                <div className="col-lg-6">
                  <section className="login-form-panel h-100">
                    <div className="login-form-panel__header">
                      <span className="text-uppercase small fw-semibold text-danger-emphasis">
                        Authentification
                      </span>
                      <h2 className="mb-2">Connexion</h2>
                      <p className="mb-0 text-secondary">
                        Saisissez votre email et votre mot de passe pour ouvrir votre espace.
                      </p>
                    </div>

                    <form className="mt-4" onSubmit={handleSubmit}>
                      <div className="mb-3">
                        <label className="form-label text-uppercase small fw-semibold" htmlFor="email">
                          Adresse email
                        </label>
                        <input
                          id="email"
                          name="email"
                          type="email"
                          autoComplete="email"
                          className="form-control form-control-lg login-input"
                          placeholder="admin@entreprise.test"
                          value={form.email}
                          onChange={handleChange}
                          required
                        />
                      </div>

                      <div className="mb-3">
                        <label className="form-label text-uppercase small fw-semibold" htmlFor="password">
                          Mot de passe
                        </label>
                        <input
                          id="password"
                          name="password"
                          type="password"
                          autoComplete="current-password"
                          className="form-control form-control-lg login-input"
                          placeholder="Votre mot de passe"
                          value={form.password}
                          onChange={handleChange}
                          required
                        />
                      </div>

                      {error ? (
                        <div className="alert alert-danger border-0 login-alert" role="alert">
                          {error}
                        </div>
                      ) : null}

                      <div className="d-grid mt-4">
                        <button className="btn btn-danger btn-lg login-submit" type="submit" disabled={submitting}>
                          {submitting ? "Connexion en cours..." : "Se connecter"}
                        </button>
                      </div>
                    </form>

                    <div className="login-form-footer">
                      <div>
                        <span className="login-form-footer__label">Environnement</span>
                        <strong>Bootstrap 5 + React</strong>
                      </div>
                      <div>
                        <span className="login-form-footer__label">Theme</span>
                        <strong>Noir / Rouge</strong>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
