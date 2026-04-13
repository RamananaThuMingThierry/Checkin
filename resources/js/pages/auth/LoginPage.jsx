import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useAuth } from "../../hooks/AuthContext";
import { resolveDefaultPath } from "../../route/guards";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login, loading } = useAuth();
  const [form, setForm] = useState({
    email: "",
    password: "",
  });
  const [error, setError] = useState("");

  async function handleSubmit(event) {
    event.preventDefault();
    setError("");

    const result = await login(form);

    if (!result.ok) {
      setError(result.message || "Email ou mot de passe invalide.");
      return;
    }

    const fallbackPath = resolveDefaultPath(result.user, result.roles);
    const nextPath = location.state?.from || fallbackPath;
    navigate(nextPath, { replace: true });
  }

  return (
    <div className="container">
      <div className="row justify-content-center align-items-center min-vh-100">
        <div className="col-12 col-md-8 col-lg-6">
          <div className="card border-0  shadow-sm rounded-3">
            <div className="row g-0">
              <div className="col-12 bg-white p-4 p-lg-5">
                <div className="d-flex align-items-center gap-3 mb-4">
                  <img
                    src="/images/logo/check-in.png"
                    alt="Pointages"
                    className="login-header__icon"
                  />

                  <div>
                    <h1 className="h3 mb-1">Pointages</h1>
                    <p className="text-body-secondary mb-0">
                      Veuillez entrer vos identifiants pour vous connecter.
                    </p>
                  </div>
                </div>

                <form onSubmit={handleSubmit}>
                  <div className="mb-3">
                    <label className="form-label" htmlFor="login-email">
                      Email
                    </label>
                    <input
                      id="login-email"
                      type="email"
                      className="form-control"
                      autoComplete="username"
                      value={form.email}
                      onChange={(event) =>
                        setForm((current) => ({ ...current, email: event.target.value }))
                      }
                      placeholder="admin@acme.test"
                      required
                    />
                  </div>

                  <div className="mb-3">
                    <label className="form-label" htmlFor="login-password">
                      Mot de passe
                    </label>
                    <input
                      id="login-password"
                      type="password"
                      className="form-control"
                      autoComplete="current-password"
                      value={form.password}
                      onChange={(event) =>
                        setForm((current) => ({ ...current, password: event.target.value }))
                      }
                      placeholder="Votre mot de passe"
                      required
                    />
                  </div>

                  {error ? (
                    <div className="alert alert-danger" role="alert">
                      {error}
                    </div>
                  ) : null}

                  <div className="d-grid">
                    <button type="submit" className="btn btn-danger" disabled={loading}>
                      {loading ? "Connexion..." : "Se connecter"}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
